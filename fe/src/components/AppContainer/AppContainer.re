open EloTypes;
open Svc;
[%bs.raw {|require('./AppContainer.scss')|}];
/* open ReasonReact; */

type state =
  | LOADING
  | LOGGED
  | NOT_LOGGED;

let component = ReasonReact.reducerComponent("contentContainer");
/* let component = ReasonReact.statelessComponent("AppContainer"); */

let initialState = () => LOADING;

let isLoggedSvc = () =>
  ReasonReact.SideEffects(
    self =>
      Js.Promise.(
        svcGet("auth/isLogged")
        |> then_(json =>
             Json.Decode.field("isLogged", Json.Decode.bool, json) |> resolve
           )
        |> then_(isLogged => self.send(SetIsLogged(isLogged)) |> resolve)
      )
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
        | LOGGED => <ContentContainer />
        | NOT_LOGGED => <LoginPage parentSend=send />
        }
      }
    </div>,
};