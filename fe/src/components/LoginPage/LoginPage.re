open EloTypes;
open Svc;
[%bs.raw {|require('./LoginPage.scss')|}];

type state = {
  password: string,
  warning: bool,
};

type parentSend = appContainerActions => unit;

type action =
  | ChangePassword(string)
  | Login(parentSend)
  | SetWarning;

let component = ReasonReact.reducerComponent("LoginPage");

let initialState = () => {password: "", warning: false};

let loginSvc = (password, parentSend) => {
  let payload =
    Json.Encode.object_([("password", Json.Encode.string(password))]);
  ReasonReact.SideEffects(
    self =>
      Js.Promise.(
        svcPost("auth/login", payload)
        |> then_(json =>
             Json.Decode.field("logged", Json.Decode.bool, json) |> resolve
           )
        |> then_(isSuccess => {
             isSuccess ?
               parentSend(SetIsLogged(true)) : self.send(SetWarning);
             resolve(true);
           })
      )
      |> ignore,
  );
};

let reducer = (action, state) =>
  switch (action) {
  | ChangePassword(password) => ReasonReact.Update({...state, password})
  | Login(parentSend) => loginSvc(state.password, parentSend)
  | SetWarning => ReasonReact.Update({...state, warning: true})
  };

let make = (~parentSend, _children) => {
  ...component,
  initialState,
  reducer,
  render: self =>
    <div className="loginPage">
      {
        self.state.warning ?
          <div className="warning">
            {ReasonReact.string("Kuuurla, zle haslo panie")}
          </div> :
          ReasonReact.null
      }
      <form
        onSubmit={
          event => {
            event |> ReactEvent.Form.preventDefault;
            self.send(Login(parentSend));
          }
        }>
        {ReasonReact.string("Podaj haslo panie kochany: ")}
        <input
          placeholder="password"
          type_="password"
          onChange={
            event =>
              self.send(ChangePassword(GameResult.valueFromEvent(event)))
          }
        />
        <button> {ReasonReact.string("login")} </button>
      </form>
    </div>,
};