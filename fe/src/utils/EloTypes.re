type user = {
  userNid: int,
  code: string,
  name: string,
  team: string,
  rating: int,
  trendRatingDiff: int,
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

type winnerLooserCodes = {
  winnerUserCode: string,
  looserUserCode: string,
};

type place =
  | RANK_AND_STATS
  | OPERATIONS
  | STATISTICS
  | NOT_FOUND;

type resultWarningOrContent('a) =
  | SUCCESS('a)
  | WARNING(string);

type resultWarningOrEmpty =
  | SUCCESS
  | WARNING(string);
