open EloTypes;
open ReasonReact;

type userForDisp = {
  userNid: int,
  code: string,
  name: string,
  rating: int,
  gamesNo: int,
  pos: int,
};

[%bs.raw {|require('./Users.scss')|}];
let component = statelessComponent("Users");

let tableRow = (userForDisp: userForDisp) =>
  <tr key={string_of_int(userForDisp.userNid)}>
    <td> {string(string_of_int(userForDisp.pos))} </td>
    <td> {string(userForDisp.code)} </td>
    <td> {string(userForDisp.name)} </td>
    <td className="gamesNo">
      {string(string_of_int(userForDisp.gamesNo))}
    </td>
    <td> {string(string_of_int(userForDisp.rating))} </td>
  </tr>;

let prepareStructure = users => {
  let prevUserForDisp = ref(None);
  List.mapi(
    (i, user: user) => {
      let lp = i + 1;

      let newPos =
        switch (prevUserForDisp^) {
        | None => lp
        | Some({rating: prevRating, pos: prevPos}) =>
          prevRating === user.rating ? prevPos : lp
        };

      let userForDisp = {
        userNid: user.userNid,
        code: user.code,
        name: user.name,
        rating: user.rating,
        gamesNo: user.gamesNo,
        pos: newPos,
      };

      prevUserForDisp := Some(userForDisp);
      userForDisp;
    },
    users,
  );
};

let make = (~users, _children) => {
  ...component,
  render: _self => {
    let usersTrs = users |> prepareStructure |> List.map(tableRow);

    <div className="users">
      <table>
        <thead>
          <tr>
            <th> {string("Pos")} </th>
            <th> {string("Code")} </th>
            <th> {string("Name")} </th>
            <th> {string("Games")} </th>
            <th> {string("Rating")} </th>
          </tr>
        </thead>
        <tbody> {usersTrs |> Array.of_list |> array} </tbody>
      </table>
    </div>;
  },
};