open EloTypes;
open Js.Promise;

open Svc;
[%bs.raw {|require('./RankAndStats.scss')|}];

type state =
  | LOADING
  | LOADED(list(user))
  | FAILURE;

let component = ReasonReact.reducerComponent("RankAndStats");

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

let renderContent = (send, users, isUsersLoading) =>
  <div>
    <div className="section">
      <Users users isUsersLoading />
      <GameResult users disable=isUsersLoading containterSend=send />
    </div>
    <hr />
    <div className="sectionLabel">
      {ReasonReact.string("Statistics for player")}
    </div>
    <div className="section">
      <RatingsHistory users disable=isUsersLoading />
    </div>
  </div>;
/* <div className="sectionLabel"> {ReasonReact.string("Ranking")} </div> */

let make = _children => {
  ...component,
  initialState,
  reducer,
  didMount: ({send}) => send(GetUsersSvc),
  render: ({state, send}) =>
    <div className="rankAndStats">
      {
        switch (state) {
        | FAILURE => <FailureMask />
        | LOADING => renderContent(send, [], true)
        | LOADED(users) => renderContent(send, users, false)
        }
      }
    </div>,
};