open Svc;
open Js.Promise;
open EloTypes;
open BsRecharts;

type status =
  | LOADING
  | LOADED(array(Js.Json.t))
  | FAILURE;

type state = {status};

type actions =
  | LoadData(list(int))
  | SetFailure
  | SetDataToState(array(Js.Json.t));

let component = ReasonReact.reducerComponent("StatisticsChart");

module ChartDataDecoder =
  ResponseDecoder.MakeWithWarningsOrContent(DecodeStatistics);

let onChartDataLoadingSuccess = (send, json) => {
  let result = ChartDataDecoder.decode(json);

  switch (result) {
  | WARNING(_msg) => send(SetFailure)
  | SUCCESS(result) => send(SetDataToState(result))
  };
};

let onChartDataLoadingError = (send, err) => {
  send(SetFailure);
  Js.Console.error(err);
};

let getChartDataSvc = checkedUsersNids => {
  let users = checkedUsersNids |> Array.of_list |> Js.Array.joinWith(",");

  ReasonReact.UpdateWithSideEffects(
    {status: LOADING},
    ({send}) =>
      svcGet("statistics/chart_data?users=" ++ users)
      |> then_(json => onChartDataLoadingSuccess(send, json) |> resolve)
      |> catch(err => onChartDataLoadingError(send, err) |> resolve)
      |> ignore,
  );
};

let renderUserLine = user => {
  let colorOpts = StatisticsColors.randomColorOptions(~seed=user.code, ());
  let color = StatisticsColors.randomColor(colorOpts);

  <Line
    key={user.userNid |> string_of_int}
    name={user.code}
    dataKey={user.code}
    stroke=color
    dot=false
    connectNulls=true
  />;
};

let filterOutcheckedUsersNids = (checkedUsersNids, user) => {
  checkedUsersNids |> List.exists(nid => nid === user.userNid);
};

let renderChart = (users, checkedUsersNids, data) => {
  let usersToshow =
    if (List.length(checkedUsersNids) === 0) {
      Array.sub(users |> Array.of_list, 0, 5);
    } else {
      List.filter(filterOutcheckedUsersNids(checkedUsersNids), users)
      |> Array.of_list;
    };

  let lines = usersToshow |> Array.map(renderUserLine) |> ReasonReact.array;

  <ResponsiveContainer height={Px(300.0)} width={Prc(100.0)}>
    <LineChart margin={"top": 0, "right": 0, "bottom": 0, "left": 0} data>
      lines
      <CartesianGrid strokeDasharray="3 3" />
      <XAxis dataKey="date" />
      <YAxis domain=[|1400, 1600|] />
      <Tooltip />
      <Legend />
    </LineChart>
  </ResponsiveContainer>;
};

let make = (~users, ~checkedUsersNids, _children) => {
  ...component,
  initialState: () => {status: LOADING},
  didMount: ({send}) => send(LoadData(checkedUsersNids)),
  willReceiveProps: ({send, state}) => {
    send(LoadData(checkedUsersNids));

    state;
  },
  reducer: (action, _state) =>
    switch (action) {
    | LoadData(nids) => getChartDataSvc(nids)
    | SetFailure => ReasonReact.Update({status: FAILURE})
    | SetDataToState(chartData) =>
      ReasonReact.Update({status: LOADED(chartData)})
    },
  render: ({state}) =>
    <div>
      {switch (state.status) {
       | FAILURE => <FailureMask />
       | LOADING => <LoadingMask />
       | LOADED(chartData) => renderChart(users, checkedUsersNids, chartData)
       }}
    </div>,
};
