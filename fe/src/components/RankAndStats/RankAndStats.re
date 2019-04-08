open EloTypes;

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

module GetUsersQuery = ReasonApollo.CreateQuery(GetUsers);

let reducer = (action: containerActions, _state) =>
  switch (action) {
  | GetUsersSvc => ReasonReact.NoUpdate
  | SetUsersToState(users) => ReasonReact.Update(LOADED(users))
  | SetFailure => ReasonReact.Update(FAILURE)
  };

let refreshUsersWithRefetch = (refetch, ()) => refetch(None)->ignore;

let renderContent = (users, isUsersLoading, refreshUsers) =>
  <div>
    <div className="section">
      <Users users isUsersLoading />
      <GameResult disable=isUsersLoading refreshUsers />
    </div>
    <hr />
    <h4> {ReasonReact.string("Statistics for player")} </h4>
    <div className="section"> <RatingsHistory disable=isUsersLoading /> </div>
  </div>;

let make = _children => {
  ...component,
  initialState,
  reducer,
  render: _self => {
    let usersQuery = GetUsers.make();

    <div className="rankAndStats">
      <GetUsersQuery
        notifyOnNetworkStatusChange=true variables=usersQuery##variables>
        ...{
             ({result, refetch, networkStatus}) => {
               let refreshUsers = refreshUsersWithRefetch(refetch);
               let isRefeching = networkStatus == Some(4);

               switch (result, isRefeching) {
               | (Loading, _)
               | (_, true) => renderContent([], true, refreshUsers)
               | (Error(_error), _) => <FailureMask />
               | (Data(response), _) =>
                 renderContent(
                   response##users->Array.to_list,
                   false,
                   refreshUsers,
                 )
               };
             }
           }
      </GetUsersQuery>
    </div>;
  },
};