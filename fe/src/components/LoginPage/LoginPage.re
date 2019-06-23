open EloTypes;
open Svc;
open Js.Promise;
open BsReactstrap;

[%bs.raw {|require('./LoginPage.scss')|}];

type warningType =
  | NOTHING
  | LOADING
  | WRONG_PASSWORD
  | SVC_FAILURE;

type state = {
  password: string,
  warning: warningType,
  passwordRef: ref(option(ReasonReact.reactRef)),
};

type parentSend = appContainerActions => unit;

type action =
  | ChangePassword(string)
  | Login(parentSend)
  | SetWarningWrongPass
  | SetWarningFailure;

let component = ReasonReact.reducerComponent("LoginPage");

let initialState = () => {
  password: "",
  warning: NOTHING,
  passwordRef: ref(None),
};

let onLoginSuccess = parentSend => {
  parentSend(SetIsLogged(true));
  DefaultPlace.setIfUrlEmpty();
};

let onServiceSuccess = (parentSend, send, json) =>
  json
  |> Json.Decode.field("logged", Json.Decode.bool)
  |> (
    isLoginSuccessfully =>
      isLoginSuccessfully ?
        onLoginSuccess(parentSend) : send(SetWarningWrongPass)
  );

let onError = (send, err) => {
  send(SetWarningFailure);
  Js.Console.error(err);
};

let loginSvc = (state, parentSend) => {
  let payload =
    Json.Encode.object_([("password", Json.Encode.string(state.password))]);
  ReasonReact.UpdateWithSideEffects(
    {...state, warning: LOADING},
    ({send}) =>
      svcPost("auth/login", payload)
      |> then_(json => onServiceSuccess(parentSend, send, json) |> resolve)
      |> catch(err => onError(send, err) |> resolve)
      |> ignore,
  );
};

let reducer = (action, state) =>
  switch (action) {
  | ChangePassword(password) => ReasonReact.Update({...state, password})
  | Login(parentSend) => loginSvc(state, parentSend)
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

let setPasswordRef = (theRef, {ReasonReact.state}) =>
  state.passwordRef := Js.Nullable.toOption(theRef);

let make = (~parentSend, _children) => {
  ...component,
  initialState,
  didMount: ({state}) =>
    switch (state.passwordRef^) {
    | Some(passwordRef) => ReasonReact.refToJsObj(passwordRef)##focus()
    | None => ()
    },
  reducer,
  render: self =>
    <div className="loginPage">
      <DocumentTitleJsx2 title={Helpers.createTitle("Login")} />
      <Navbar className="menu" color="light" light=true>
        <NavbarBrand href="/"> {"Eloapp" |> ReasonReact.string} </NavbarBrand>
      </Navbar>
      {
        switch (self.state.warning) {
        | NOTHING => ReasonReact.null
        | WRONG_PASSWORD => warningWrongPass()
        | LOADING => <LoadingMask />
        | SVC_FAILURE => <FailureMask />
        }
      }
      <Form
        onSubmit={
          event => {
            event |> ReactEvent.Form.preventDefault;
            self.send(Login(parentSend));
          }
        }>
        <Container>
          <Row>
            <Col>
              <Input
                innerRef={self.handle(setPasswordRef)}
                placeholder="password"
                type_="password"
                onChange={
                  event =>
                    self.send(
                      ChangePassword(GameResult.valueFromEvent(event)),
                    )
                }
              />
            </Col>
            <Col>
              <Button color="primary"> {ReasonReact.string("Login")} </Button>
            </Col>
          </Row>
        </Container>
      </Form>
    </div>,
};