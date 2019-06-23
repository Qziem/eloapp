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
    usersStatus: LOADING,
    chartDataStatus: LOADED([||]),
    checkedUsersNids: [],
    chartDataLoading: true,
  },
  didMount: ({send}) => send(LoadUsers),
  reducer: (action, state) =>
    switch (action) {
    | SetStatsChartFailure =>
      ReasonReact.Update({...state, usersStatus: FAILURE})
    | SetDataToState(chartData) =>
      ReasonReact.Update({
        ...state,
        chartDataLoading: false,
        chartDataStatus: LOADED(chartData),
      })
    | LoadUsers => StatisticsChartUsersSvc.get(state)
    | LoadData(nids) => StatisticsChartDataSvc.get(state, nids)
    | UpdateCheckedNids(checkedUsersNids) =>
      ReasonReact.Update({...state, checkedUsersNids})
    | SetUsersInState(users) =>
      let checkedUsersNids =
        Array.sub(Array.of_list(users), 0, 5)
        |> Array.map(user => user.userNid)
        |> Array.to_list;

      ReasonReact.UpdateWithSideEffects(
        {...state, usersStatus: LOADED(users), checkedUsersNids},
        ({send}) => send(LoadData(checkedUsersNids)),
      );
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
           | FAILURE => <FailureMask />
           }}
          {state.chartDataLoading
             ? <div className="statistics_chart__loading">
                 <LoadingMask />
               </div>
             : ReasonReact.null}
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