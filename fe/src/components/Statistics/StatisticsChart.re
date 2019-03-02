open ReasonReact;
open EloTypes;
open BsRecharts;

let component = statelessComponent("StatisticsChart");

let renderUserLine = user => {
  let colorOpts = StatisticsColors.randomColorOptions(~seed=user.code, ());
  let color = StatisticsColors.randomColor(colorOpts);

  <Line
    key={user.userNid |> string_of_int}
    name={user.code}
    dataKey={user.code}
    stroke=color
    dot=false
    connectNulls=true
  />;
};

let make = (~chartData: array(Js.Json.t), ~users: array(user), _children) => {
  ...component,
  render: _self => {
    <ResponsiveContainer height={Px(200.0)} width={Prc(100.0)}>
      <LineChart
        margin={"top": 0, "right": 0, "bottom": 0, "left": 0} data=chartData>
        {users |> Array.map(renderUserLine) |> ReasonReact.array}
        <CartesianGrid strokeDasharray="3 3" />
        <XAxis dataKey="date" />
        <YAxis domain=[|1400, 1600|] />
        <Tooltip />
        <Legend />
      </LineChart>
    </ResponsiveContainer>;
  },
};
