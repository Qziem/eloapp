open EloTypes;
open Js.Promise;

open Svc;
[%bs.raw {|require('./ContentContainer.scss')|}];

type state =
  | LOADING
  | LOADED(list(user))
  | FAILURE;

let component = ReasonReact.reducerComponent("ContentContainer");

let initialState = () => LOADING;

let onSuccess = (send, json) =>
  json |> DecodeUsers.users |> (users => send(SetUsersToState(users)));

let onError = (send, err) => {
  send(SetFailure);
  Js.Console.error(err);
};

let getUsersSvc = () =>
  ReasonReact.UpdateWithSideEffects(
    LOADING,
    ({send}) =>
      svcGet("users")
      |> then_(json => onSuccess(send, json) |> resolve)
      |> catch(err => onError(send, err) |> resolve)
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
              {ReasonReact.string("Statistics for player")}
            </div>
            <div className="section"> <RatingsHistory users /> </div>
            <hr />
            <hr />
            <div className="sectionSmallLabel">
              {ReasonReact.string("Add player")}
            </div>
            <div className="section"> <AddPlayer containterSend=send /> </div>
            <hr />
            <div className="sectionSmallLabel">
              {ReasonReact.string("Remove last game")}
            </div>
            <div className="section"> <RemoveGame containterSend=send /> </div>
          </div>
        }
      }
    </div>,
};