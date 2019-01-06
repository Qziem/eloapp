open EloTypes;
open ReasonReact;
open BsReactstrap;

type userForDisp = {
  userNid: int,
  code: string,
  name: string,
  team: string,
  rating: int,
  pos: int,
};

[%bs.raw {|require('./Users.scss')|}];
let component = statelessComponent("Users");

let prepareTeamIcons = teamName => {
  let icon =
    switch (teamName) {
    | "krew" => "fas fa-tint"
    | "inkwizycja" => "fas fa-shield-alt"
    | "ops" => "fas fa-user-secret"
    | "quality" => "far fa-thumbs-up"
    | "devops" => "fas fa-tools"
    | _ => "far fa-smile"
    };

  <span className=icon />;
};

let tableRow = (userForDisp: userForDisp) =>
  <tr key={string_of_int(userForDisp.userNid)}>
    <td className="posTd"> {string(string_of_int(userForDisp.pos))} </td>
    <td className="codeTd"> {string(userForDisp.code)} </td>
    <td className="nameTd"> {string(userForDisp.name)} </td>
    <td className="teamTd"> {userForDisp.team |> prepareTeamIcons} </td>
    <td className="ratingTd">
      {string(string_of_int(userForDisp.rating))}
    </td>
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
        team: user.team,
        rating: user.rating,
        pos: newPos,
      };

      prevUserForDisp := Some(userForDisp);
      userForDisp;
    },
    users,
  );
};

let renderContent = users => {
  let usersTrs = users |> prepareStructure |> List.map(tableRow);

  <div className="users">
    <Table size="sm">
      <thead>
        <tr>
          <th className="posTh"> {string("Pos")} </th>
          <th className="codeTh"> {string("Code")} </th>
          <th className="nameTh"> {string("Name")} </th>
          <th className="teamTh"> {string("Team")} </th>
          <th className="ratingTh"> {string("Rating")} </th>
        </tr>
      </thead>
      <tbody> {usersTrs |> Array.of_list |> array} </tbody>
    </Table>
  </div>;
};

let make = (~users, ~isUsersLoading, _children) => {
  ...component,
  render: _self => isUsersLoading ? <LoadingMask /> : renderContent(users),
};
