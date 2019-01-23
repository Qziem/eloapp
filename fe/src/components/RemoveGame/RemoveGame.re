[%bs.raw {|require('./RemoveGame.scss')|}];
open Svc;
open Js.Promise;
open BsReactstrap;

type saveStateType =
  | NOTHING
  | SAVING
  | FAILURE
  | WARNING(string)
  | SUCCESS;

type state = {
  code: string,
  saveState: saveStateType,
};

type action =
  | ChangeCode(string)
  | RemoveGame
  | SetSuccess
  | SetWarning(string)
  | SetFailure
  | ClearSaveState;

type removeGameResult =
  | SUCCESS
  | WARNING(string);

exception IllegalCombinationInRemoveGameResult;

let component = ReasonReact.reducerComponent("RemoveGame");

let initialState = () => {code: "", saveState: NOTHING};

let decodeRemoveGameResult = json => {
  let status = Json.Decode.(json |> field("status", string));
  let warningMsg =
    Json.Decode.(json |> optional(field("warningMsg", string)));

  switch (status, warningMsg) {
  | ("success", None) => SUCCESS
  | ("warning", Some(msg)) => WARNING(msg)
  | _ => raise(IllegalCombinationInRemoveGameResult)
  };
};

let onSuccess = (send, json) => {
  let removeGameResult = decodeRemoveGameResult(json);

  switch (removeGameResult) {
  | SUCCESS => send(SetSuccess)
  | WARNING(msg) => send(SetWarning(msg))
  };
};

let onError = (send, err) => {
  send(SetFailure);
  Js.Console.error(err);
};

let removeGameSvc = (state, code) => {
  let payload = {| {} |} |> Json.parseOrRaise;

  ReasonReact.UpdateWithSideEffects(
    {...state, saveState: SAVING},
    ({send}) => {
      let url = "remove_game/" ++ code;
      svcDelete(url, payload)
      |> then_(json => onSuccess(send, json) |> resolve)
      |> catch(err => onError(send, err) |> resolve)
      |> ignore;
    },
  );
};

let removeGameReducer = state =>
  switch (String.trim(state.code)) {
  | "" =>
    ReasonReact.SideEffects(
      (({send}) => send(SetWarning("Code can not be empty"))),
    )
  | code => removeGameSvc(state, code)
  };

let reducer = (action, state) =>
  switch (action) {
  | ChangeCode(code) => ReasonReact.Update({...state, code})
  | RemoveGame => removeGameReducer(state)
  | SetSuccess => ReasonReact.Update({code: "", saveState: SUCCESS})
  | SetWarning(msg) =>
    ReasonReact.Update({...state, saveState: WARNING(msg)})
  | SetFailure => ReasonReact.Update({...state, saveState: FAILURE})
  | ClearSaveState => ReasonReact.Update({...state, saveState: NOTHING})
  };

let onYesClick = (send, ()) => send(RemoveGame);

let hanldeDismissAlert = (send, ()) => send(ClearSaveState);

let make = _children => {
  ...component,
  initialState,
  reducer,
  render: ({state, send}) =>
    <div className="removeGame">
      {
        switch (state.saveState) {
        | SUCCESS =>
          <Alert color="success" toggle={hanldeDismissAlert(send)}>
            {"Successfully removed :)" |> ReasonReact.string}
          </Alert>
        | WARNING(msg) =>
          <Alert color="warning" toggle={hanldeDismissAlert(send)}>
            {msg |> ReasonReact.string}
          </Alert>
        | FAILURE => <FailureMask />
        | SAVING => <LoadingMask />
        | NOTHING => ReasonReact.null
        }
      }
      <Container>
        <Row>
          <Col xs=4>
            <Input
              placeholder="code"
              value={state.code}
              onChange={
                event => send(ChangeCode(GameResult.valueFromEvent(event)))
              }
            />
          </Col>
          <Col>
            <ButtonWithConfirm
              disabled={
                switch (state.saveState) {
                | SAVING => true
                | _ => false
                }
              }
              label="Remove"
              onYesClick={onYesClick(send)}
            />
          </Col>
        </Row>
      </Container>
    </div>,
};