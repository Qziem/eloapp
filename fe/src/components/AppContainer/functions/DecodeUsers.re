open Types;

let user = user =>
  Json.Decode.{
    userNid: field("user_nid", int, user),
    code: field("code", string, user),
    name: field("name", string, user),
    rating: field("rating", int, user),
    gamesNo: field("games_no", int, user),
  };

let users = json: list(user) => Json.Decode.list(user, json);