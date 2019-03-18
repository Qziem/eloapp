open EloTypes;
open StatisticsChartContainerTypes;

[%bs.raw {|require('./StatisticsChart.scss')|}];

let component = ReasonReact.reducerComponent(__FILE__);

let toggleUser = (send, userNid) => {
  send(ToggleCheckbox(userNid));
};

let make = _children => {
  ...component,
  initialState: () => {
    chartDataStatus: LOADING,
    usersStatus: LOADING,
    checkedUsersNids: [],
  },
  didMount: ({send}) => send(LoadUsers),
  reducer: (action, state) =>
    switch (action) {
    | SetStatsChartFailure =>
      ReasonReact.Update({...state, usersStatus: FAILURE})
    | SetDataToState(chartData) =>
      ReasonReact.Update({...state, chartDataStatus: LOADED(chartData)})
    | LoadUsers => StatisticsChartUsersSvc.get(state)
    | LoadData(nids) => StatisticsChartDataSvc.get(state, nids)
    | UpdateCheckedNids(checkedUsersNids) =>
      ReasonReact.Update({...state, checkedUsersNids})
    | SetUsersInState(users) =>
      let checkedUsersNids =
        Array.sub(Array.of_list(users), 0, 5)
        |> Array.map(user => user.userNid)
        |> Array.to_list;

      ReasonReact.Update({
        ...state,
        usersStatus: LOADED(users),
        checkedUsersNids,
      });
    | ToggleCheckbox(userNid) =>
      let isChecked =
        List.exists(nid => nid === userNid, state.checkedUsersNids);

      let checkedUsersNids =
        if (isChecked) {
          state.checkedUsersNids |> List.filter(nid => nid !== userNid);
        } else {
          state.checkedUsersNids |> List.append([userNid]);
        };

      ReasonReact.UpdateWithSideEffects(
        {...state, checkedUsersNids},
        ({send}) => send(LoadData(checkedUsersNids)),
      );
    },
  render: ({state, send}) => {
    switch (state.usersStatus) {
    | FAILURE => <FailureMask />
    | LOADING => <LoadingMask />
    | LOADED(users) =>
      <>
        <div className="statistics_chart">
          {switch (state.chartDataStatus) {
           | LOADED(chartData) =>
             <StatisticsChart
               users
               chartData
               checkedUsersNids={state.checkedUsersNids}
             />
           | LOADING => <LoadingMask />
           | FAILURE => <FailureMask />
           }}
        </div>
        <StatisticsChartUsers
          users
          checkedUsersNids={state.checkedUsersNids}
          toggleUser={toggleUser(send)}
        />
      </>
    };
  },
};
