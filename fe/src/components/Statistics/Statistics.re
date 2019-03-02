open Svc;
open EloTypes;
open Js.Promise;

[%bs.raw {|require('./Statistics.scss')|}];

type status =
  | LOADING
  | LOADED(list(user), array(Js.Json.t))
  | FAILURE;

type state = {
  status,
  users: list(user),
  chartData: array(Js.Json.t),
  checkedUsers: list(int),
};

type actions =
  | LoadUsers
  | LoadData
  | SetUsersInState(list(user))
  | SetDataToState(array(Js.Json.t))
  | ToggleCheckbox(int)
  | ThrowError;

let component = ReasonReact.reducerComponent("Statistics");

module ChartDataDecoder =
  ResponseDecoder.MakeWithWarningsOrContent(DecodeStatistics);

let onUserLoadSuccess = (send, json) => {
  json |> DecodeUsers.users |> (users => send(SetUsersInState(users)));

  send(LoadData);
};

let onUserLoadError = (send, err) => {
  send(ThrowError);
  Js.Console.error(err);
};

let getUsersSvc = state =>
  ReasonReact.UpdateWithSideEffects(
    {...state, status: LOADING},
    ({send}) =>
      svcGet("users")
      |> then_(json => onUserLoadSuccess(send, json) |> resolve)
      |> catch(err => onUserLoadError(send, err) |> resolve)
      |> ignore,
  );

let onChartDataLoadingSuccess = (send, json) => {
  let result = ChartDataDecoder.decode(json);

  switch (result) {
  | WARNING(_msg) => send(ThrowError)
  | SUCCESS(result) => send(SetDataToState(result))
  };
};

let onChartDataLoadingError = (send, err) => {
  send(ThrowError);
  Js.Console.error(err);
};

let getChartDataSvc = state => {
  let users = state.checkedUsers |> Array.of_list |> Js.Array.joinWith(",");

  ReasonReact.UpdateWithSideEffects(
    {...state, status: LOADING},
    ({send}) =>
      svcGet("statistics/chart_data?users=" ++ users)
      |> then_(json => onChartDataLoadingSuccess(send, json) |> resolve)
      |> catch(err => onChartDataLoadingError(send, err) |> resolve)
      |> ignore,
  );
};

let reducer = (action: actions, state) =>
  switch (action) {
  | LoadUsers => getUsersSvc(state)
  | LoadData => getChartDataSvc(state)
  | SetUsersInState(users) =>
    let checkedUsers =
      Array.sub(Array.of_list(users), 0, 5)
      |> Array.map(user => user.userNid)
      |> Array.to_list;

    ReasonReact.Update({
      ...state,
      users,
      checkedUsers,
      status: LOADED(users, state.chartData),
    });
  | SetDataToState(chartData) =>
    ReasonReact.Update({
      ...state,
      chartData,
      status: LOADED(state.users, chartData),
    })
  | ToggleCheckbox(userNid) =>
    let isChecked = List.exists(nid => nid === userNid, state.checkedUsers);

    let checkedUsers =
      if (isChecked) {
        state.checkedUsers |> List.filter(nid => nid !== userNid);
      } else {
        state.checkedUsers |> List.append([userNid]);
      };

    ReasonReact.UpdateWithSideEffects(
      {...state, checkedUsers},
      ({send}) => send(LoadData),
    );
  | ThrowError => ReasonReact.Update({...state, status: FAILURE})
  };

let toggleCheckedUser = (state, userNid) => {
  List.exists(item => item === userNid, state.checkedUsers) === false;
};

let renderCheckbox = (state, send, user) => {
  <label className="statistics_chart_users__user" key={user.code}>
    <input
      className="statistics_chart_users__input"
      type_="checkbox"
      value={user.userNid |> string_of_int}
      checked={state.checkedUsers |> List.exists(nid => nid === user.userNid)}
      onChange={_event => send(ToggleCheckbox(user.userNid))}
    />
    <span> {user.code |> ReasonReact.string} </span>
  </label>;
};

let filterOutCheckedUsers = (checkedUsers, user) => {
  checkedUsers |> List.exists(nid => nid === user.userNid);
};

let renderStatistics = (users, data, state, send) => {
  let usersToshow =
    if (List.length(state.checkedUsers) === 0) {
      Array.sub(users |> Array.of_list, 0, 5);
    } else {
      List.filter(filterOutCheckedUsers(state.checkedUsers), users)
      |> Array.of_list;
    };

  let checkboxes =
    users |> List.map(renderCheckbox(state, send)) |> Array.of_list;

  <div>
    {"Stats" |> ReasonReact.string}
    <StatisticsChart chartData=data users=usersToshow />
    <div className="statistics_chart_users">
      {checkboxes |> ReasonReact.array}
    </div>
  </div>;
};

let make = _children => {
  ...component,
  initialState: () => {
    status: LOADING,
    users: [],
    chartData: [||],
    checkedUsers: [],
  },
  reducer,
  didMount: ({send}) => send(LoadUsers),
  render: ({state, send}) =>
    <div>
      {switch (state.status) {
       | FAILURE => <FailureMask />
       | LOADING => <LoadingMask />
       | LOADED(users, userStats) =>
         renderStatistics(users, userStats, state, send)
       }}
    </div>,
};
