open EloTypes;

open Svc;
[%bs.raw {|require('./ContentContainer.scss')|}];

type state =
  | LOADING
  | LOADED(list(user))
  | FAILURE;

let component = ReasonReact.reducerComponent("ContentContainer");

let initialState = () => LOADING;

let getUsersSvc = () =>
  ReasonReact.UpdateWithSideEffects(
    LOADING,
    self =>
      Js.Promise.(
        svcGet("users")
        |> then_(json => DecodeUsers.users(json) |> resolve)
        |> then_(users => self.send(SetUsersToState(users)) |> resolve)
        |> catch(_err => {
             self.send(SetFailure);
             resolve();
           })
      )
      |> ignore,
  );

let reducer = (action, _state) =>
  switch (action) {
  | GetUsersSvc => getUsersSvc()
  | SetUsersToState(users) => ReasonReact.Update(LOADED(users))
  | SetFailure => ReasonReact.Update(FAILURE)
  };

let make = _children => {
  ...component,
  initialState,
  reducer,
  didMount: ({send}) => send(GetUsersSvc),
  render: ({state, send}) =>
    <div className="contentContainer">
      {
        switch (state) {
        | LOADING =>
          <div className="contentLoadingMsg">
            {ReasonReact.string("Loading data...")}
          </div>
        | FAILURE => <FailureMask />
        | LOADED(users) =>
          <div>
            <div className="sectionLabel">
              {ReasonReact.string("Ranking")}
            </div>
            <div className="section">
              <Users users />
              <GameResult users containterSend=send />
            </div>
            <hr />
            <div className="sectionLabel">
              {ReasonReact.string("Add player")}
            </div>
            <div className="section"> <AddPlayer containterSend=send /> </div>
            <hr />
            <div className="sectionLabel">
              {ReasonReact.string("Statistics for player")}
            </div>
            <div className="section"> <RatingsHistory users /> </div>
          </div>
        }
      }
    </div>,
};