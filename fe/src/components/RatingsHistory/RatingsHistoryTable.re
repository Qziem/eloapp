open EloTypes;
open ReasonReact;

/* [%bs.raw {|require('./Users.scss')|}]; */
let component = statelessComponent("RatingsHistoryTable");

let tableRow = (i, ratingHistoryWithWin) => {
  let {rating, date, isWin} = ratingHistoryWithWin;
  let classe = isWin ? "win" : "loose";
  <tr key={string_of_int(i + 1)} className=classe>
    <td className="lpTd"> {string(string_of_int(i + 1))} </td>
    <td className="ratingTd"> {string(string_of_int(rating))} </td>
    <td className="dateTd"> {string(date)} </td>
  </tr>;
};

let calcWinsOrLooses = (ratingsHistoryWithWin, isWinCalc) =>
  List.fold_left(
    (acc, ratingHistory) => {
      let addVal = ratingHistory.isWin === isWinCalc ? 1 : 0;
      acc + addVal;
    },
    0,
    ratingsHistoryWithWin,
  );

let make = (~ratingsHistoryWithWin, _children) => {
  ...component,
  render: _self => {
    let ratingsHistoryTrs = ratingsHistoryWithWin |> List.mapi(tableRow);

    <div className="ratingsHistoryTable">
      <div className="topBar">
        <span className="winsLabel"> {"Wins: " |> ReasonReact.string} </span>
        <span className="winsSum">
          {
            calcWinsOrLooses(ratingsHistoryWithWin, true)
            - 1
            |> string_of_int
            |> ReasonReact.string
          }
        </span>
        <span className="loosesLabel">
          {"Looses: " |> ReasonReact.string}
        </span>
        <span className="loosesSum">
          {
            calcWinsOrLooses(ratingsHistoryWithWin, false)
            |> string_of_int
            |> ReasonReact.string
          }
        </span>
      </div>
      <table>
        <thead>
          <tr>
            <th> {string("Lp")} </th>
            <th> {string("Rating")} </th>
            <th> {string("Date")} </th>
          </tr>
        </thead>
        <tbody> {ratingsHistoryTrs |> Array.of_list |> array} </tbody>
      </table>
    </div>;
  },
};