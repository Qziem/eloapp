open EloTypes;
open Svc;
open Js.Promise;

[%bs.raw {|require('bootstrap/dist/css/bootstrap.min.css')|}];
[%bs.raw {|require('./AppContainer.scss')|}];

type state =
  | LOADING
  | FAILURE
  | LOGGED
  | NOT_LOGGED;

let component = ReasonReact.reducerComponent("contentContainer");

let initialState = () => LOADING;

let onSuccess = (send, json) =>
  json
  |> Json.Decode.field("isLogged", Json.Decode.bool)
  |> (isLogged => send(SetIsLogged(isLogged)));

let onError = (send, err) => {
  send(SetFailure);
  Js.Console.error(err);
};

let isLoggedSvc = () =>
  ReasonReact.SideEffects(
    ({send}) =>
      svcGet("auth/isLogged")
      |> then_(json => onSuccess(send, json) |> resolve)
      |> catch(err => onError(send, err) |> resolve)
      |> ignore,
  );

let reducer = (action, _state) =>
  switch (action) {
  | IsLogged => isLoggedSvc()
  | SetIsLogged(true) => ReasonReact.Update(LOGGED)
  | SetIsLogged(false) => ReasonReact.Update(NOT_LOGGED)
  | SetFailure => ReasonReact.Update(FAILURE)
  };

let make = _children => {
  ...component,
  initialState,
  reducer,
  didMount: ({send}) => send(IsLogged),
  render: ({state, send}) =>
    <div className="appContainer">
      {switch (state) {
       | LOADING => <LoadingMask />
       | NOT_LOGGED => <LoginPage parentSend=send />
       | FAILURE => <FailureMask />
       | LOGGED => <Layout appSend=send />
       }}
    </div>,
};