open EloTypes;
open Svc;
[%bs.raw {|require('./AppContainer.scss')|}];
/* open ReasonReact; */

type state = {isLogged: option(bool)};

let component = ReasonReact.reducerComponent("contentContainer");
/* let component = ReasonReact.statelessComponent("AppContainer"); */

let initialState = () => {isLogged: None};

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
  | SetIsLogged(isLogged) => ReasonReact.Update({isLogged: Some(isLogged)})
  };

let make = _children => {
  ...component,
  initialState,
  reducer,
  didMount: self => self.send(IsLogged),
  render: self =>
    <div className="appContainer">
      {
        switch (self.state.isLogged) {
        | None => ReasonReact.string("")
        | Some(true) => <ContentContainer />
        | Some(false) => <LoginPage parentSend={self.send} />
        }
      }
    </div>,
};