open Types;
[%bs.raw {|require('./GameResult.scss')|}];

type action =
  | ChangeWinUser(string)
  | ChangeLooseUser(string)
  | UpdateClick(list(user), containerActions => unit)
  | UpdateRatingsSvc(containerActions => unit);

let component = ReasonReact.reducerComponent("GameResult");

let initialState = () => {
  userWinCode: "",
  userLooseCode: "",
  warningMsg: None,
  saving: false,
};

let handleUpdateClickReducer = (state, users, containterSend) => {
  let winCode = state.userWinCode;
  let looseCode = state.userLooseCode;
  let winUserExist =
    List.exists(user => CalcRatings.compareCodes(user.code, winCode), users);
  let looseUserExist =
    List.exists(
      user => CalcRatings.compareCodes(user.code, looseCode),
      users,
    );

  switch (winUserExist, looseUserExist) {
  | (true, true) =>
    ReasonReact.UpdateWithSideEffects(
      {...state, warningMsg: None},
      (self => self.send(UpdateRatingsSvc(containterSend))),
    )
  | (true, false) =>
    ReasonReact.Update({...state, warningMsg: Some("Looser doesen't exist")})
  | (false, true) =>
    ReasonReact.Update({...state, warningMsg: Some("Winner doesen't exist")})
  | (false, false) =>
    ReasonReact.Update({
      ...state,
      warningMsg: Some("Players doesen't exist"),
    })
  };
};

let updateRatingsSvc = (state, containterSend) =>
  ReasonReact.UpdateWithSideEffects(
    {...state, saving: true},
    _self => {
      let payload = EncodeUpdateRatings.encode(state);
      Js.Promise.(
        Svc.svcPost("users/update_ratings", payload)
        |> then_(_resp => containterSend(GetUsersSvc) |> resolve)
      )
      |> ignore;
    },
  );

let reducer = (action, state) =>
  switch (action) {
  | ChangeWinUser(value) =>
    ReasonReact.Update({...state, userWinCode: value})
  | ChangeLooseUser(value) =>
    ReasonReact.Update({...state, userLooseCode: value})
  | UpdateClick(users, containterSend) =>
    handleUpdateClickReducer(state, users, containterSend)
  | UpdateRatingsSvc(containterSend) =>
    updateRatingsSvc(state, containterSend)
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
              {"Saving in progress" |> ReasonReact.string}
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
            </tr>
          </tbody>
        </table>
        <button disabled={self.state.saving} type_="submit">
          {ReasonReact.string("update")}
        </button>
      </form>
    </div>,
};