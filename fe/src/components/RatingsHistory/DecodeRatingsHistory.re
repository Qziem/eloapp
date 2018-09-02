open EloTypes;

let ratingHistoryDec = ratingHistory =>
  Json.Decode.{
    rating: field("rating", int, ratingHistory),
    date: field("cdate", string, ratingHistory),
  };

let ratingsHistoryDec = json: list(ratingHistory) =>
  Json.Decode.list(ratingHistoryDec, json);