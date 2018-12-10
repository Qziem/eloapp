open EloTypes;
open Helpers;
open Svc;
open Js.Promise;

[%bs.raw {|require('./GameResult.scss')|}];

type saveStateType =
  | NOTHING
  | SAVING
  | WARNING(string)
  | SUCCESS;

type state = {
  userWinnerCode: string,
  userLooserCode: string,
  saveState: saveStateType,
};

type action =
  | ChangeWinUser(string)
  | ChangeLooseUser(string)
  | UpdateClick;

let component = ReasonReact.reducerComponent("GameResult");

let initialState = () => {
  userWinnerCode: "",
  userLooserCode: "",
  saveState: NOTHING,
};

let onSuccess = (containterSend, _resp) => containterSend(GetUsersSvc);

let updateRatingsSvc = (state, users, containterSend) => {
  let winnerLooserNids = {
    winnerUserNid: getUserNidFromCode(state.userWinnerCode, users),
    looserUserNid: getUserNidFromCode(state.userLooserCode, users),
  };

  ReasonReact.UpdateWithSideEffects(
    {...state, saveState: SAVING},
    _self => {
      let payload = EncodeUpdateRatings.encode(winnerLooserNids);
      svcPut("users/update_ratings", payload)
      |> then_(json => onSuccess(containterSend, json) |> resolve)
      |> ignore;
    },
  );
};

let updateStateWarningMsg = (state, msg: string) =>
  ReasonReact.Update({...state, saveState: WARNING(msg)});

let handleUpdateClickReducer = (state, users, containterSend) => {
  let winCode = state.userWinnerCode;
  let looseCode = state.userLooserCode;
  let winUserExist =
    List.exists(user => compareCodes(user.code, winCode), users);
  let looseUserExist =
    List.exists(user => compareCodes(user.code, looseCode), users);

  switch (winUserExist, looseUserExist) {
  | (true, true) =>
    !compareCodes(winCode, looseCode) ?
      updateRatingsSvc(state, users, containterSend) :
      updateStateWarningMsg(state, "Codes are the same")

  | (true, false) => updateStateWarningMsg(state, "Looser doesen't exist")
  | (false, true) => updateStateWarningMsg(state, "Winner doesen't exist")
  | (false, false) => updateStateWarningMsg(state, "Players doesen't exist")
  };
};

let reducer = (users, containterSend, action, state) =>
  switch (action) {
  | ChangeWinUser(value) =>
    ReasonReact.Update({...state, userWinnerCode: value})
  | ChangeLooseUser(value) =>
    ReasonReact.Update({...state, userLooserCode: value})
  | UpdateClick => handleUpdateClickReducer(state, users, containterSend)
  };

let valueFromEvent = event => ReactEvent.Form.target(event)##value;

let make = (~users, ~containterSend, _children) => {
  ...component,
  initialState,
  reducer: reducer(users, containterSend),
  render: self =>
    <div className="gameResult">
      <div className="messageContainer">
        {
          switch (self.state.saveState) {
          | SUCCESS =>
            <div className="success">
              {"Successfully updated :)" |> ReasonReact.string}
            </div>
          | WARNING(msg) =>
            <div className="warning"> {msg |> ReasonReact.string} </div>
          | SAVING =>
            <div className="saving">
              {"Saving in progress..." |> ReasonReact.string}
            </div>
          | NOTHING => ReasonReact.null
          }
        }
      </div>
      <form
        onSubmit={
          event => {
            event |> ReactEvent.Form.preventDefault;
            self.send(UpdateClick);
          }
        }>
        <table>
          <thead>
            <tr>
              <th> {ReasonReact.string("Winner")} </th>
              <th> {ReasonReact.string("Looser")} </th>
              <th />
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <input
                  placeholder="code"
                  onChange={
                    event => self.send(ChangeWinUser(valueFromEvent(event)))
                  }
                />
              </td>
              <td>
                <input
                  placeholder="code"
                  onChange={
                    event =>
                      self.send(ChangeLooseUser(valueFromEvent(event)))
                  }
                />
              </td>
              <td>
                <button
                  disabled={self.state.saveState === SAVING} type_="submit">
                  {ReasonReact.string("Update")}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </form>
    </div>,
};