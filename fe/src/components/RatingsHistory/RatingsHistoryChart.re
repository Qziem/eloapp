open ReasonReact;
open BsRecharts;
open EloTypes;

let component = statelessComponent("RatingsHistoryChart");

let make = (~ratingsHistory, _children) => {
  ...component,
  render: _self => {
    let data =
      ratingsHistory
      |> List.rev
      |> List.map(item => {"name": item.date, "rating": item.userRating})
      |> Array.of_list;

    <ResponsiveContainer height={Px(200.0)} width={Prc(100.0)}>
      <LineChart margin={"top": 0, "right": 0, "bottom": 0, "left": 0} data>
        <Line name="Rating" dataKey="rating" stroke="#ff7f02" />
        <CartesianGrid strokeDasharray="3 3" />
        <YAxis domain=[|1400, 1600|] />
        <Tooltip />
        <Legend />
      </LineChart>
    </ResponsiveContainer>;
  },
};
