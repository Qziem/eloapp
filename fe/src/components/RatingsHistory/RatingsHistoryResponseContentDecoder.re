open EloTypes;
open Json.Decode;

type content = list(ratingHistory);
let contentKey = "ratingsHistory";

let ratingHistory = json => {
  userRating: field("userRating", int, json),
  oponentName: field("oponentName", string, json),
  oponentRating: field("oponentRating", int, json),
  ratingDiff: field("ratingDiff", int, json),
  date: field("date", string, json),
};

let decode = json => Json.Decode.list(ratingHistory, json);