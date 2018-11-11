type user = {
  userNid: int,
  code: string,
  name: string,
  rating: int,
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
  | SetIsLogged(bool);

type winnerLooserNids = {
  winnerUserNid: int,
  looserUserNid: int,
};