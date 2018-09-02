type user = {
  userNid: int,
  code: string,
  name: string,
  gamesNo: int,
  rating: int,
};

type ratingHistory = {
  rating: int,
  date: string,
};

type containerActions =
  | GetUsersSvc
  | SetUsersToState(list(user));

type appContainerActions =
  | IsLogged
  | SetIsLogged(bool);

type winnerLooserNids = {
  winnerUserNid: int,
  looserUserNid: int,
};