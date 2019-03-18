open Svc;
open Js.Promise;
open StatisticsChartContainerTypes;

module ChartDataDecoder =
  ResponseDecoder.MakeWithWarningsOrContent(DecodeStatistics);

let onSuccess = (send, json) => {
  let result = ChartDataDecoder.decode(json);

  switch (result) {
  | WARNING(_msg) => send(SetStatsChartFailure)
  | SUCCESS(result) => send(SetDataToState(result))
  };
};

let onError = err => {
  Js.Console.error(err);
};

let get = (state, checkedUsersNids) => {
  let users = checkedUsersNids |> Array.of_list |> Js.Array.joinWith(",");

  ReasonReact.UpdateWithSideEffects(
    {...state, chartDataStatus: LOADING},
    ({send}) =>
      svcGet("statistics/chart_data?users=" ++ users)
      |> then_(json => onSuccess(send, json) |> resolve)
      |> catch(err => onError(err) |> resolve)
      |> ignore,
  );
};
