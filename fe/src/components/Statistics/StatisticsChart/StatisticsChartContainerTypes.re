open EloTypes;

type usersStatus =
  | LOADING
  | FAILURE
  | LOADED(list(user));

type chartDataStatus =
  | LOADING
  | LOADED(array(Js.Json.t))
  | FAILURE;

type state = {
  usersStatus,
  chartDataStatus,
  checkedUsersNids: list(int),
};

type actions =
  | LoadUsers
  | UpdateCheckedNids(list(int))
  | SetUsersInState(list(user))
  | ToggleCheckbox(int)
  | SetStatsChartFailure
  | LoadData(list(int))
  | SetDataToState(array(Js.Json.t));
