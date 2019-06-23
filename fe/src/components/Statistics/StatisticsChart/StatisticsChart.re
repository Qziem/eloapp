open EloTypes;
open BsRecharts;

let component = ReasonReact.statelessComponent(__FILE__);

let renderUserLine = user => {
  let color =
    RandomColorRe.randomColor(
      ~seed=user.code,
      ~luminosity=RandomColorRe.LuminosityDark,
      (),
    );

  <Line
    key={user.userNid |> string_of_int}
    name={user.code}
    dataKey={user.code}
    stroke=color
    dot=false
    connectNulls=true
  />;
};

let renderChart = (users, checkedUsersNids, data) => {
  let filterOutcheckedUsersNids = (checkedUsersNids, user) => {
    checkedUsersNids |> List.exists(nid => nid === user.userNid);
  };

  let usersToshow =
    List.length(checkedUsersNids) === 0
      ? []
      : (checkedUsersNids |> filterOutcheckedUsersNids)->List.filter(users);

  let lines =
    usersToshow
    |> Array.of_list
    |> Array.map(renderUserLine)
    |> ReasonReact.array;

  <ResponsiveContainer height={Px(300.0)} width={Prc(100.0)}>
    <LineChart margin={"top": 0, "right": 0, "bottom": 0, "left": 0} data>
      lines
      <CartesianGrid strokeDasharray="3 3" />
      <XAxis dataKey="date" />
      <YAxis domain=[|1400, 1600|] />
      <Tooltip />
      <Legend />
    </LineChart>
  </ResponsiveContainer>;
};

let make = (~users, ~checkedUsersNids, ~chartData, _children) => {
  ...component,
  render: _ => renderChart(users, checkedUsersNids, chartData),
};