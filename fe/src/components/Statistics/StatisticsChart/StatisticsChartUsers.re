open EloTypes;

let component = ReasonReact.statelessComponent(__FILE__);

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

let make = (~checkedUsersNids, ~users, ~toggleUser, _children) => {
  ...component,
  render: _ => {
    let checkboxes =
      users
      |> List.map(renderCheckbox(checkedUsersNids, toggleUser))
      |> Array.of_list
      |> ReasonReact.array;

    <div className="statistics_chart_users"> checkboxes </div>;
  },
};
