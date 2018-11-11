open EloTypes;
open Svc;
[%bs.raw {|require('./LoginPage.scss')|}];

type warningType =
  | NOTHING
  | WRONG_PASSWORD
  | SVC_FAILURE;

type state = {
  password: string,
  warning: warningType,
};

type parentSend = appContainerActions => unit;

type action =
  | ChangePassword(string)
  | Login(parentSend)
  | SetWarningWrongPass
  | SetWarningFailure;

let component = ReasonReact.reducerComponent("LoginPage");

let initialState = () => {password: "", warning: NOTHING};

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
               parentSend(SetIsLogged(true)) :
               self.send(SetWarningWrongPass);
             resolve(true);
           })
        |> catch(err => {
             self.send(SetWarningFailure);
             Js.Console.error(err);
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
  | SetWarningWrongPass =>
    ReasonReact.Update({...state, warning: WRONG_PASSWORD})
  | SetWarningFailure => ReasonReact.Update({...state, warning: SVC_FAILURE})
  };

let warningWrongPass = () =>
  <div className="wrongPass">
    FailureMask.imgNosacz
    <div className="label">
      {"Kuurla, zle haslo panie!" |> ReasonReact.string}
      <br />
      {"Podaj dobre i bedzie pryma sort" |> ReasonReact.string}
    </div>
  </div>;

let make = (~parentSend, _children) => {
  ...component,
  initialState,
  reducer,
  render: self =>
    <div className="loginPage">
      {
        switch (self.state.warning) {
        | NOTHING => ReasonReact.null
        | WRONG_PASSWORD => warningWrongPass()
        | SVC_FAILURE => <FailureMask />
        }
      }
      <form
        onSubmit={
          event => {
            event |> ReactEvent.Form.preventDefault;
            self.send(Login(parentSend));
          }
        }>
        {ReasonReact.string("Podaj haslo: ")}
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