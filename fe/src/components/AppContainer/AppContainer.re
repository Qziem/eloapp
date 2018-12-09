open EloTypes;
open Svc;
open Js.Promise;

[%bs.raw {|require('./AppContainer.scss')|}];

type state =
  | LOADING
  | LOGGED
  | NOT_LOGGED;

let component = ReasonReact.reducerComponent("contentContainer");

let initialState = () => LOADING;

let onSuccess = (send, json) =>
  json
  |> Json.Decode.field("isLogged", Json.Decode.bool)
  |> (
    isLogged => {
      send(SetIsLogged(isLogged));
      isLogged ? DefaultPlace.setIfUrlEmpty() : ();
    }
  );

let isLoggedSvc = () =>
  ReasonReact.SideEffects(
    ({send}) =>
      svcGet("auth/isLogged")
      |> then_(json => onSuccess(send, json) |> resolve)
      |> ignore,
  );

let reducer = (action, _state) =>
  switch (action) {
  | IsLogged => isLoggedSvc()
  | SetIsLogged(true) => ReasonReact.Update(LOGGED)
  | SetIsLogged(false) => ReasonReact.Update(NOT_LOGGED)
  };

let make = _children => {
  ...component,
  initialState,
  reducer,
  didMount: ({send}) => send(IsLogged),
  render: ({state, send}) =>
    <div className="appContainer">
      {
        switch (state) {
        | LOADING =>
          <div className="isLoggedloadingMsg">
            {"Checking is logged..." |> ReasonReact.string}
          </div>
        | NOT_LOGGED => <LoginPage parentSend=send />
        | LOGGED => <Layout />
        }
      }
    </div>,
};