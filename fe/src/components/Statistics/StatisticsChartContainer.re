open Svc;
open EloTypes;
open Js.Promise;

[%bs.raw {|require('./StatisticsChart.scss')|}];

type status =
  | LOADING
  | FAILURE
  | LOADED(list(user));

type state = {
  status,
  checkedUsersNids: list(int),
  users: list(user),
};

type actions =
  | LoadUsers
  | UpdateCheckedNids(list(int))
  | SetUsersInState(list(user))
  | ToggleCheckbox(int)
  | SetFailure;

let component = ReasonReact.reducerComponent("StatisticsChartContainer");

let onUserLoadSuccess = (send, json) => {
  json |> DecodeUsers.users |> (users => send(SetUsersInState(users)));
};

let onUserLoadError = (send, err) => {
  send(SetFailure);
  Js.Console.error(err);
};

let getUsersSvc = state =>
  ReasonReact.UpdateWithSideEffects(
    {...state, status: LOADING},
    ({send}) =>
      svcGet("users")
      |> then_(json => onUserLoadSuccess(send, json) |> resolve)
      |> catch(err => onUserLoadError(send, err) |> resolve)
      |> ignore,
  );

let toggleUser = (send, userNid) => {
  send(ToggleCheckbox(userNid));
};

let make = _children => {
  ...component,
  initialState: () => {status: LOADING, checkedUsersNids: [], users: []},
  didMount: ({send}) => send(LoadUsers),
  reducer: (action, state) =>
    switch (action) {
    | SetFailure => ReasonReact.Update({...state, status: FAILURE})
    | LoadUsers => getUsersSvc(state)
    | UpdateCheckedNids(checkedUsersNids) =>
      ReasonReact.Update({...state, checkedUsersNids})
    | SetUsersInState(users) =>
      let checkedUsersNids =
        Array.sub(Array.of_list(users), 0, 5)
        |> Array.map(user => user.userNid)
        |> Array.to_list;

      ReasonReact.Update({status: LOADED(users), users, checkedUsersNids});
    | ToggleCheckbox(userNid) =>
      let isChecked =
        List.exists(nid => nid === userNid, state.checkedUsersNids);

      let checkedUsersNids =
        if (isChecked) {
          state.checkedUsersNids |> List.filter(nid => nid !== userNid);
        } else {
          state.checkedUsersNids |> List.append([userNid]);
        };

      ReasonReact.Update({...state, checkedUsersNids});
    },

  render: ({state, send}) =>
    <div>
      {switch (state.status) {
       | FAILURE => <FailureMask />
       | LOADING => <LoadingMask />
       | LOADED(users) =>
         <div>
           <StatisticsChart users checkedUsersNids={state.checkedUsersNids} />
           <StatisticsChartUsers
             users
             checkedUsersNids={state.checkedUsersNids}
             toggleUser={toggleUser(send)}
           />
         </div>
       }}
    </div>,
};
