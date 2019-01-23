open Svc;
open Js.Promise;
open BsReactstrap;

[%bs.raw {|require('./AddPlayer.scss')|}];

type savingState =
  | NOTHING
  | SAVING
  | FAILURE
  | WARNING(string)
  | SUCCESS;

type state = {
  code: string,
  name: string,
  savingState,
};

type action =
  | ChangeCode(string)
  | ChangeName(string)
  | SetSavingState(savingState)
  | AddPlayer;

let component = ReasonReact.reducerComponent("AddPlayer");

let initialState = () => {code: "", name: "", savingState: NOTHING};

let onSuccess = send => send(SetSavingState(SUCCESS));

let onError = (send, err) => {
  send(SetSavingState(FAILURE));
  Js.Console.error(err);
};

let addPlayerSvc = state => {
  let payload =
    Json.Encode.object_([
      ("code", state.code |> String.trim |> Json.Encode.string),
      ("name", state.name |> String.trim |> Json.Encode.string),
    ]);
  ReasonReact.UpdateWithSideEffects(
    {...state, savingState: SAVING},
    ({send}) =>
      svcPost("users", payload)
      |> then_(_json => onSuccess(send) |> resolve)
      |> catch(err => onError(send, err) |> resolve)
      |> ignore,
  );
};

let addPlayerReducer = state => {
  let codeValid = String.trim(state.code) !== "";
  let nameValid = String.trim(state.name) !== "";
  switch (codeValid, nameValid) {
  | (true, true) => addPlayerSvc(state)
  | _ =>
    ReasonReact.Update({
      ...state,
      savingState: WARNING("Fields can not be empty"),
    })
  };
};

let reducer = (action, state) =>
  switch (action) {
  | ChangeCode(code) => ReasonReact.Update({...state, code})
  | ChangeName(name) => ReasonReact.Update({...state, name})
  | SetSavingState(SUCCESS) =>
    ReasonReact.Update({code: "", name: "", savingState: SUCCESS})
  | SetSavingState(savingState) =>
    ReasonReact.Update({...state, savingState})
  | AddPlayer => addPlayerReducer(state)
  };

let hanldeDismissAlert = (send, ()) => send(SetSavingState(NOTHING));

let make = _children => {
  ...component,
  initialState,
  reducer,
  render: ({state, send}) =>
    <div className="addPlayer">
      {
        switch (state.savingState) {
        | NOTHING => ReasonReact.null
        | SUCCESS =>
          <Alert color="success" toggle={hanldeDismissAlert(send)}>
            {"Successfully saved :)" |> ReasonReact.string}
          </Alert>
        | SAVING => <LoadingMask />
        | FAILURE => <FailureMask />
        | WARNING(msg) =>
          <Alert color="warning" toggle={hanldeDismissAlert(send)}>
            {ReasonReact.string(msg)}
          </Alert>
        }
      }
      <Form
        onSubmit={
          event => {
            event |> ReactEvent.Form.preventDefault;
            send(AddPlayer);
          }
        }>
        <Container>
          <Row>
            <Col>
              <Input
                placeholder="code"
                value={state.code}
                onChange={
                  event =>
                    send(ChangeCode(GameResult.valueFromEvent(event)))
                }
              />
            </Col>
            <Col>
              <FormGroup>
                <Input
                  placeholder="name"
                  value={state.name}
                  onChange={
                    event =>
                      send(ChangeName(GameResult.valueFromEvent(event)))
                  }
                />
              </FormGroup>
            </Col>
            <Col>
              <Button color="primary"> {ReasonReact.string("Add")} </Button>
            </Col>
          </Row>
        </Container>
      </Form>
    </div>,
};