type user = {
  userNid: int,
  code: string,
  name: string,
  team: string,
  rating: int,
  lastSummaryRatingDiff: int,
};

type ratingHistory = {
  userRating: int,
  oponentName: string,
  oponentRating: int,
  ratingDiff: int,
  date: string,
};

type containerActions =
  | GetUsersSvc
  | SetUsersToState(list(user))
  | SetFailure;

type appContainerActions =
  | IsLogged
  | SetIsLogged(bool)
  | SetFailure;

type winnerLooserNids = {
  winnerUserNid: int,
  looserUserNid: int,
};

type place =
  | RANK_AND_STATS
  | OPERATIONS
  | NOT_FOUND;