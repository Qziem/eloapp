[%bs.raw {|require('./RemoveGame.scss')|}];
open Svc;
open EloTypes;

type saveStateType =
  | NOTHING
  | SAVING
  | WARNING(string);

type state = {
  code: string,
  saveState: saveStateType,
};

type action =
  | ChangeCode(string)
  | RemoveGame
  | SetSaved
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

let removeGameSvc = (state, users) => {
  let userNid = Helpers.getUserNidFromCode(state.code, users);
  let payload = {| {} |} |> Json.parseOrRaise;

  ReasonReact.UpdateWithSideEffects(
    {...state, saveState: SAVING},
    self =>
      Js.Promise.(
        svcDelete("remove_game/" ++ string_of_int(userNid), payload)
        |> then_(result => decodeRemoveGameResult(result) |> resolve)
        |> then_(removeGameResult => {
             switch (removeGameResult) {
             | {removed: true, warning: None} => self.send(SetSaved)
             | {removed: false, warning: Some(msg)} =>
               self.send(SetWarning(msg))
             | _ => raise(IllegalCombinationInRemoveGameResult)
             };

             resolve();
           })
      )
      |> ignore,
  );
};

let removeGameReducer = (state, users) => {
  let userExist =
    List.exists(
      (user: user) => Helpers.compareCodes(user.code, state.code),
      users,
    );

  userExist ?
    removeGameSvc(state, users) :
    ReasonReact.Update({
      ...state,
      saveState: WARNING("Player doesn't exist"),
    });
};
let reducer = (users, containterSend, action, state) =>
  switch (action) {
  | ChangeCode(code) => ReasonReact.Update({...state, code})
  | RemoveGame => removeGameReducer(state, users)
  | SetSaved =>
    containterSend(GetUsersSvc);
    ReasonReact.NoUpdate;
  | SetWarning(msg) =>
    ReasonReact.Update({...state, saveState: WARNING(msg)})
  };

let onYesClick = (send, ()) => send(RemoveGame);

let make = (~users, ~containterSend, _children) => {
  ...component,
  initialState,
  reducer: reducer(users, containterSend),
  render: ({state, send}) =>
    <div className="removeGame">
      {
        switch (state.saveState) {
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