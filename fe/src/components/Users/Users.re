open EloTypes;
open ReasonReact;
open BsReactstrap;

type userForDisplay = {
  userNid: int,
  code: string,
  name: string,
  team: string,
  rating: int,
  pos: int,
  trendRatingDiff: int,
};

type trend =
  | UP
  | STAY
  | DOWN;

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

  <span title=teamName className=icon />;
};

let getTrend = trendRatingDiff =>
  switch (trendRatingDiff) {
  | ratingDiff when ratingDiff < 0 => DOWN
  | ratingDiff when ratingDiff == 0 => STAY
  | _ => UP
  };

let trendColumn = trendRatingDiff => {
  let trend = getTrend(trendRatingDiff);
  let classNameTd =
    switch (trend) {
    | DOWN => "down"
    | STAY => "stay"
    | UP => "up"
    };

  let classNameIcon =
    switch (trend) {
    | DOWN => "fas fa-arrow-down"
    | STAY => "fas fa-circle"
    | UP => "fas fa-arrow-up"
    };

  <td className={"trendRatingDiffTd " ++ classNameTd}>
    <span className=classNameIcon />
    <span className="ratingDiffValue">
      {string(string_of_int(trendRatingDiff))}
    </span>
  </td>;
};

let tableRow = (userForDisplay: userForDisplay) =>
  <tr key={string_of_int(userForDisplay.userNid)}>
    <td className="posTd"> {string(string_of_int(userForDisplay.pos))} </td>
    <td className="codeTd"> {string(userForDisplay.code)} </td>
    <td className="nameTd"> {string(userForDisplay.name)} </td>
    <td className="teamTd"> {userForDisplay.team |> prepareTeamIcons} </td>
    <td className="ratingTd">
      {string(string_of_int(userForDisplay.rating))}
    </td>
    {trendColumn(userForDisplay.trendRatingDiff)}
  </tr>;

let prepareStructure = users => {
  let prevUserForDisplay = ref(None);
  List.mapi(
    (i, user: user) => {
      let lp = i + 1;

      let newPos =
        switch (prevUserForDisplay^) {
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
        trendRatingDiff: user.trendRatingDiff,
      };

      prevUserForDisplay := Some(userForDisp);
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
          <th className="trendRatingDiffTh">
            <span title="Trend calculated from last 7 days">
              {string("Trend")}
            </span>
          </th>
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