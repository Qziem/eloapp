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

let onError = (send: containerActions => unit, err) => {
  send(SetFailure);
  Js.Console.error(err);
};

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

module GetUsersQuery = ReasonApolloQuery.Make(GetUsers);

[@bs.module] external gql: ReasonApolloTypes.gql = "graphql-tag";
let queryObj: ApolloClient.queryObj = {
  "query": gql(. userNameQuery##query),
  "variables": userNameQuery##variables,
};

let getUsersSvc = () =>
  ReasonReact.UpdateWithSideEffects(
    LOADING,
    ({send}) =>
      Client.instance##query(queryObj)
      |> then_(resp =>
           (
             switch (resp->GetUsersQuery.convertJsInputToReason.result) {
             | Data(da) =>
               let users = da##users |> Array.to_list;
               send(SetUsersToState(users));
             }
           )
           |> resolve
         )
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

/* {
     switch (state) {
     | FAILURE => <FailureMask />
     | LOADING => renderContent(send, [], true)
     | LOADED(users) => renderContent(send, users, false)
     }
   } */

/* <ReasonApollo.Provider client=Client.instance>
     <GetUsersQuery>
       ...{
            ({result}) =>
              switch (result) {
              | Loading => <div> {ReasonReact.string("Loading")} </div>
              | Error(error) =>
                <div> {ReasonReact.string(error##message)} </div>
              | Data(response) =>
                Js.log(response);
                ReasonReact.string("resp");
              }
          }
     </GetUsersQuery>
   </ReasonApollo.Provider> */