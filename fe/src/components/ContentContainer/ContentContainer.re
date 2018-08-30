open EloTypes;

open Svc;
[%bs.raw {|require('./ContentContainer.scss')|}];
/* open ReasonReact; */
type state = {users: option(list(user))};

let component = ReasonReact.reducerComponent("ContentContainer");

let initialState = () => {users: None};

let getUsersSvc = () =>
  ReasonReact.UpdateWithSideEffects(
    {users: None},
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
  | SetUsersToState(users) => ReasonReact.Update({users: Some(users)})
  };

let make = _children => {
  ...component,
  initialState,
  reducer,
  didMount: self => self.send(GetUsersSvc),
  render: self =>
    <div className="contentContainer">
      {
        switch (self.state.users) {
        | None =>
          <div className="loadingMsg">
            {ReasonReact.string("Loading data")}
          </div>
        | Some(users) =>
          <div>
            <div className="sectionLabel">
              {ReasonReact.string("Ranking")}
            </div>
            <div className="section">
              <Users users />
              <GameResult users containterSend={self.send} />
            </div>
            <hr />
            <div className="sectionLabel">
              {ReasonReact.string("Add player")}
            </div>
            <div className="section">
              <AddPlayer containterSend={self.send} />
            </div>
            <hr />
            <div className="sectionLabel">
              {ReasonReact.string("Statistics for player")}
            </div>
            <div className="section"> <Stats users /> </div>
          </div>
        }
      }
    </div>,
};