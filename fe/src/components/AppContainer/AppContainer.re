open Types;
open Svc;
[%bs.raw {|require('./AppContainer.scss')|}];
/* open ReasonReact; */
type state = {users: list(user)};

let component = ReasonReact.reducerComponent("AppContainer");

let initialState = () => {users: []};

let getUsersSvc = () =>
  ReasonReact.UpdateWithSideEffects(
    {users: []},
    self =>
      Js.Promise.(
        svcGet("users")
        |> then_(json => DecodeUsers.users(json) |> resolve)
        |> then_(users => self.send(SetUsersToState(users)) |> resolve)
      )
      |> ignore,
  );

let reducer = (action, _state) =>
  switch (action) {
  | GetUsersSvc => getUsersSvc()
  | SetUsersToState(users) => ReasonReact.Update({users: users})
  };

let make = _children => {
  ...component,
  initialState,
  reducer,
  didMount: self => self.send(GetUsersSvc),
  render: self =>
    <div className="appContainer">
      {
        switch (List.length(self.state.users)) {
        | 0 =>
          <div className="loadingMsg">
            {ReasonReact.string("Loading data")}
          </div>
        | _ =>
          <div>
            <div className="sectionLabel">
              {ReasonReact.string("Ranking")}
            </div>
            <div className="section">
              <Users users={self.state.users} />
              <GameResult
                users={self.state.users}
                containterSend={self.send}
              />
            </div>
            <hr />
            <div className="sectionLabel">
              {ReasonReact.string("Add player")}
            </div>
            <hr />
            <div className="sectionLabel">
              {ReasonReact.string("Statistics for player")}
            </div>
          </div>
        }
      }
    </div>,
};