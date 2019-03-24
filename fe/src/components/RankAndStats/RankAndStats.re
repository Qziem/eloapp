open EloTypes;
open Js.Promise;

[%bs.raw {|require('./RankAndStats.scss')|}];

type state =
  | LOADING
  | LOADED(list(user))
  | FAILURE;

let component = ReasonReact.reducerComponent("RankAndStats");

let initialState = () => LOADING;

module GetUsers = [%graphql
  {|
  query {
    users @bsRecord {
      userNid
      code,
      name,
      rating,
      team,
      trendRatingDiff,
    }
  }
|}
];

let userNameQuery = GetUsers.make();

module ClientGetUsers = ClientNoComponent.Make(GetUsers);

let onSuccess = (send, result: ReasonApolloTypes.queryResponse(GetUsers.t)) =>
  switch (result) {
  | Data(response) =>
    let users = response##users |> Array.to_list;
    send(SetUsersToState(users));
  | Loading => ()
  | Error(_) => send(SetFailure)
  };

let getUsersSvc = () =>
  ReasonReact.UpdateWithSideEffects(
    LOADING,
    ({send}) =>
      ClientGetUsers.call(userNameQuery)
      |> then_(result => result |> onSuccess(send) |> resolve)
      |> ignore,
  );

let reducer = (action: containerActions, _state) =>
  switch (action) {
  | GetUsersSvc => getUsersSvc()
  | SetUsersToState(users) => ReasonReact.Update(LOADED(users))
  | SetFailure => ReasonReact.Update(FAILURE)
  };

let renderContent = (send, users, isUsersLoading) =>
  <div>
    <div className="section">
      <Users users isUsersLoading />
      <GameResult disable=isUsersLoading containterSend=send />
    </div>
    <hr />
    <h4> {ReasonReact.string("Statistics for player")} </h4>
    <div className="section"> <RatingsHistory disable=isUsersLoading /> </div>
  </div>;

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