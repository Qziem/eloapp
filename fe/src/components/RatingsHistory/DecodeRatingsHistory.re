open EloTypes;

let ratingHistoryDec = ratingHistory =>
  Json.Decode.{
    userRating: field("userRating", int, ratingHistory),
    oponentName: field("oponentName", string, ratingHistory),
    oponentRating: field("oponentRating", int, ratingHistory),
    ratingDiff: field("ratingDiff", int, ratingHistory),
    date: field("date", string, ratingHistory),
  };

let ratingsHistoryDec = json: list(ratingHistory) =>
  Json.Decode.list(ratingHistoryDec, json);