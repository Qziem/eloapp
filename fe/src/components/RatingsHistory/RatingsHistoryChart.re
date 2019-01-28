open ReasonReact;
open BsRecharts;
open EloTypes;

let component = statelessComponent("RatingsHistoryChart");

let sumRatingWithDiff = (rating: ratingHistory): int => {
  rating.userRating + rating.ratingDiff;
};

let calculateActualRating = (ratingsHistory: list(ratingHistory)): int => {
  ratingsHistory |> List.hd |> sumRatingWithDiff;
};

let addRating = (name: string, rating: int, historyList) => {
  let actualRatingObj = {"name": name, "rating": rating};

  [actualRatingObj, ...historyList];
};

let make = (~ratingsHistory, _children) => {
  ...component,
  render: _self => {
    let actualRating = ratingsHistory |> calculateActualRating;

    let data =
      ratingsHistory
      |> List.map(item => {"name": item.date, "rating": item.userRating})
      |> addRating("actual", actualRating)
      |> List.rev
      |> Array.of_list;

    <ResponsiveContainer height={Px(200.0)} width={Prc(100.0)}>
      <LineChart margin={"top": 0, "right": 0, "bottom": 0, "left": 0} data>
        <Line name="Rating" dataKey="rating" stroke="#ff7f02" dot=false />
        <CartesianGrid strokeDasharray="3 3" />
        <YAxis domain=[|1400, 1600|] />
        <Tooltip />
        <Legend />
      </LineChart>
    </ResponsiveContainer>;
  },
};
