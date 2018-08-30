/* open EloTypes;
   open Svc; */
[%bs.raw {|require('./AppContainer.scss')|}];
/* open ReasonReact; */
/* type state = {isLogged: bool}; */

/* let component = ReasonReact.reducerComponent("contentContainer"); */
let component = ReasonReact.statelessComponent("AppContainer");

/* let initialState = () => {isLogged: true}; */
/* let reducer = (_action, _state) => ReasonReact.NoUpdate; */

let make = _children => {
  ...component,
  /* initialState,
     reducer, */
  /* didMount: self => self.send(GetUsersSvc), */
  render: _self => <div className="appContainer"> <ContentContainer /> </div>,
};