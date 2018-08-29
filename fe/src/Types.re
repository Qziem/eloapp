type user = {
  userNid: int,
  code: string,
  name: string,
  gamesNo: int,
  rating: int,
};

type containerActions =
  | GetUsersSvc
  | SetUsersToState(list(user));

type gameResultState = {
  userWinCode: string,
  userLooseCode: string,
  warningMsg: option(string),
  saving: bool,
};