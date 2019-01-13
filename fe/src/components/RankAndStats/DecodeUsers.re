open EloTypes;
open Json.Decode;

let user = user => {
  userNid: field("userNid", int, user),
  code: field("code", string, user),
  name: field("name", string, user),
  team: field("team", string, user),
  rating: field("rating", int, user),
  lastSummaryRatingDiff: field("lastSummaryRatingDiff", int, user),
};

let users = json: list(user) => Json.Decode.list(user, json);