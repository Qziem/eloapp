open EloTypes;

[%bs.raw {|require('./RankAndStats.scss')|}];

let refetchingStatus = Some(4);
let component = ReasonReact.statelessComponent("RankAndStats");

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

let reloadUsersListWithRefetch = (refetch, ()) => refetch(None)->ignore;

let renderContent = (users, isUsersLoading, reloadUsersList) =>
  <div>
    <div className="section">
      <Users users isUsersLoading />
      <GameResult disable=isUsersLoading reloadUsersList />
    </div>
    <hr />
    <h4> {ReasonReact.string("Statistics for player")} </h4>
    <div className="section"> <RatingsHistory disable=isUsersLoading /> </div>
  </div>;

let make = _children => {
  ...component,
  render: _self => {
    let usersQuery = GetUsers.make();

    <div className="rankAndStats">
      <GetUsersQuery
        notifyOnNetworkStatusChange=true variables=usersQuery##variables>
        ...{
             ({result, refetch, networkStatus}) => {
               let reloadUsersList = reloadUsersListWithRefetch(refetch);
               let isRefeching = networkStatus == refetchingStatus;

               switch (result, isRefeching) {
               | (Loading, _)
               | (_, true) => renderContent([], true, reloadUsersList)
               | (Error(_error), _) => <FailureMask />
               | (Data(response), _) =>
                 renderContent(
                   response##users->Array.to_list,
                   false,
                   reloadUsersList,
                 )
               };
             }
           }
      </GetUsersQuery>
    </div>;
  },
};