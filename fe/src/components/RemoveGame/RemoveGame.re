[%bs.raw {|require('./RemoveGame.scss')|}];
open Svc;
open Js.Promise;

type saveStateType =
  | NOTHING
  | SAVING
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
  | SetWarning(string);

type removeGameResult = {
  removed: bool,
  warning: option(string),
};

exception IllegalCombinationInRemoveGameResult;

let component = ReasonReact.reducerComponent("RemoveGame");

let initialState = () => {code: "", saveState: NOTHING};

let decodeRemoveGameResult = json =>
  Json.Decode.{
    removed: json |> field("removed", bool),
    warning: json |> optional(field("warning", string)),
  };

let onSuccess = (send, json) =>
  json
  |> decodeRemoveGameResult
  |> (
    removeGameResult =>
      switch (removeGameResult) {
      | {removed: true, warning: None} => send(SetSuccess)
      | {removed: false, warning: Some(msg)} => send(SetWarning(msg))
      | _ => raise(IllegalCombinationInRemoveGameResult)
      }
  );

let removeGameSvc = state => {
  let payload = {| {} |} |> Json.parseOrRaise;

  ReasonReact.UpdateWithSideEffects(
    {...state, saveState: SAVING},
    ({send}) => {
      let url = "remove_game/" ++ state.code;
      svcDelete(url, payload)
      |> then_(json => onSuccess(send, json) |> resolve)
      |> ignore;
    },
  );
};

let reducer = (action, state) =>
  switch (action) {
  | ChangeCode(code) => ReasonReact.Update({...state, code})
  | RemoveGame => removeGameSvc(state)
  | SetSuccess => ReasonReact.Update({...state, saveState: SUCCESS})
  | SetWarning(msg) =>
    ReasonReact.Update({...state, saveState: WARNING(msg)})
  };

let onYesClick = (send, ()) => send(RemoveGame);

let make = _children => {
  ...component,
  initialState,
  reducer,
  render: ({state, send}) =>
    <div className="removeGame">
      {
        switch (state.saveState) {
        | SUCCESS =>
          <div className="success">
            {"Successfully removed :)" |> ReasonReact.string}
          </div>
        | WARNING(msg) =>
          <div className="warning"> {msg |> ReasonReact.string} </div>
        | SAVING =>
          <div className="saving">
            {"Removing in progress..." |> ReasonReact.string}
          </div>
        | NOTHING => ReasonReact.null
        }
      }
      <table>
        <tbody>
          <tr>
            <td>
              <input
                placeholder="code"
                value={state.code}
                onChange={
                  event =>
                    send(ChangeCode(GameResult.valueFromEvent(event)))
                }
              />
            </td>
            <td>
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
            </td>
          </tr>
        </tbody>
      </table>
    </div>,
};