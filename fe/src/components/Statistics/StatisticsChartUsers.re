open EloTypes;

let component = ReasonReact.statelessComponent("StatisticsChartUsers");

let toggleCheckedUser = (checkedUsersNids, userNid) => {
  List.exists(item => item === userNid, checkedUsersNids) === false;
};

let renderCheckbox = (checkedUsersNids, toggleUser, user) => {
  <label className="statistics_chart_users__user" key={user.code}>
    <input
      className="statistics_chart_users__input"
      type_="checkbox"
      value={user.userNid |> string_of_int}
      checked={checkedUsersNids |> List.exists(nid => nid === user.userNid)}
      onChange={_event => toggleUser(user.userNid)}
    />
    <span> {user.code |> ReasonReact.string} </span>
  </label>;
};

let filterOutcheckedUsersNids = (checkedUsersNids, user) => {
  checkedUsersNids |> List.exists(nid => nid === user.userNid);
};

let renderUsers = (users, checkedUsersNids, toggleUser) => {
  let checkboxes =
    users
    |> List.map(renderCheckbox(checkedUsersNids, toggleUser))
    |> Array.of_list;

  <div className="statistics_chart_users">
    {checkboxes |> ReasonReact.array}
  </div>;
};

let make = (~checkedUsersNids, ~users, ~toggleUser, _children) => {
  ...component,
  render: _ =>
    <div> {renderUsers(users, checkedUsersNids, toggleUser)} </div>,
};
