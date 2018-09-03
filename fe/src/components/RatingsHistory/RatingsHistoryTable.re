open EloTypes;
open ReasonReact;

/* [%bs.raw {|require('./Users.scss')|}]; */
let component = statelessComponent("RatingsHistoryTable");

let tableRow = (i, ratingsHistory) =>
  <tr key={string_of_int(i + 1)}>
    <td className="lpTd"> {string(string_of_int(i + 1))} </td>
    <td className="ratingTd">
      {string(string_of_int(ratingsHistory.rating))}
    </td>
    <td className="dateTd"> {string(ratingsHistory.date)} </td>
  </tr>;

let make = (~ratingsHistory, _children) => {
  ...component,
  render: _self => {
    let ratingsHistoryTrs = ratingsHistory |> List.mapi(tableRow);

    <div className="ratingsHistoryTable">
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