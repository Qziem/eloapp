open Svc;
open Js.Promise;
open StatisticsChartContainerTypes;

let onSuccess = (checkedUsersNids, send, json) => {
  json |> DecodeUsers.users |> (users => send(SetUsersInState(users)));
  send(LoadData(checkedUsersNids));
};

let onError = (send, err) => {
  send(SetStatsChartFailure);
  Js.Console.error(err);
};

let get = state =>
  ReasonReact.UpdateWithSideEffects(
    {...state, usersStatus: LOADING},
    ({send}) =>
      svcGet("users")
      |> then_(json =>
           onSuccess(state.checkedUsersNids, send, json) |> resolve
         )
      |> catch(err => onError(send, err) |> resolve)
      |> ignore,
  );
