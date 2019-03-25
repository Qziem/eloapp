open Svc;
open Js.Promise;
open StatisticsChartContainerTypes;

let onSuccess = (send, json) => {
  let content =
    json |> Json.Decode.field("data", Json.Decode.array(json => json));

  send(SetDataToState(content));
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
