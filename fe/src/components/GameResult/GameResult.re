open EloTypes;
open Helpers;
open Svc;
open Js.Promise;

[%bs.raw {|require('./GameResult.scss')|}];

type action =
  | ChangeWinUser(string)
  | ChangeLooseUser(string)
  | UpdateClick(list(user), containerActions => unit)
  | UpdateRatingsSvc(winnerLooserNids, containerActions => unit);

type gameResultState = {
  userWinnerCode: string,
  userLooserCode: string,
  warningMsg: option(string),
  saving: bool,
};

let component = ReasonReact.reducerComponent("GameResult");

let initialState = () => {
  userWinnerCode: "",
  userLooserCode: "",
  warningMsg: None,
  saving: false,
};

let updateStateWarningMsg = (state: gameResultState, msg: string) =>
  ReasonReact.Update({...state, warningMsg: Some(msg)});

let clearWarningAndSendUpdateRatingAction = (state, users, containterSend) =>
  ReasonReact.UpdateWithSideEffects(
    {...state, warningMsg: None},
    self => {
      let winnerLooserNids = {
        winnerUserNid: getUserNidFromCode(state.userWinnerCode, users),
        looserUserNid: getUserNidFromCode(state.userLooserCode, users),
      };
      self.send(UpdateRatingsSvc(winnerLooserNids, containterSend));
    },
  );

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
      clearWarningAndSendUpdateRatingAction(state, users, containterSend) :
      updateStateWarningMsg(state, "Codes are the same")

  | (true, false) => updateStateWarningMsg(state, "Looser doesen't exist")
  | (false, true) => updateStateWarningMsg(state, "Winner doesen't exist")
  | (false, false) => updateStateWarningMsg(state, "Players doesen't exist")
  };
};

let onSuccess = (containterSend, _resp) => containterSend(GetUsersSvc);

let updateRatingsSvc = (winnerLooserNids, state, containterSend) =>
  ReasonReact.UpdateWithSideEffects(
    {...state, saving: true},
    _self => {
      let payload = EncodeUpdateRatings.encode(winnerLooserNids);
      svcPut("users/update_ratings", payload)
      |> then_(json => onSuccess(containterSend, json) |> resolve)
      |> ignore;
    },
  );

let reducer = (action, state) =>
  switch (action) {
  | ChangeWinUser(value) =>
    ReasonReact.Update({...state, userWinnerCode: value})
  | ChangeLooseUser(value) =>
    ReasonReact.Update({...state, userLooserCode: value})
  | UpdateClick(users, containterSend) =>
    handleUpdateClickReducer(state, users, containterSend)
  | UpdateRatingsSvc(winnerLooserNids, containterSend) =>
    updateRatingsSvc(winnerLooserNids, state, containterSend)
  };

let valueFromEvent = event => ReactEvent.Form.target(event)##value;

let make = (~users, ~containterSend, _children) => {
  ...component,
  initialState,
  reducer,
  render: self =>
    <div className="gameResult">
      <div className="messageContainer">
        {
          switch (self.state.warningMsg) {
          | Some(msg) =>
            <div className="warrning"> {msg |> ReasonReact.string} </div>
          | None => ReasonReact.null
          }
        }
        {
          self.state.saving ?
            <div className="saving">
              {"Saving in progress..." |> ReasonReact.string}
            </div> :
            ReasonReact.null
        }
      </div>
      <form
        onSubmit={
          event => {
            event |> ReactEvent.Form.preventDefault;
            self.send(UpdateClick(users, containterSend));
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
                <button disabled={self.state.saving} type_="submit">
                  {ReasonReact.string("Update")}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </form>
    </div>,
};