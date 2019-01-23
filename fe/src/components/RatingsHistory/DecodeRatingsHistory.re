open EloTypes;

type ratingsHistoryResult =
  | SUCCESS(list(ratingHistory))
  | WARNING(string);

exception IllegalCombinationInRatingsHistoryResult;

let ratingHistory = json =>
  Json.Decode.{
    userRating: field("userRating", int, json),
    oponentName: field("oponentName", string, json),
    oponentRating: field("oponentRating", int, json),
    ratingDiff: field("ratingDiff", int, json),
    date: field("date", string, json),
  };

let ratingHistoryList = json: list(ratingHistory) =>
  Json.Decode.list(ratingHistory, json);

let response = json => {
  let status = Json.Decode.(json |> field("status", string));
  let ratingHistoryList =
    Json.Decode.(
      json |> optional(field("ratingsHistory", ratingHistoryList))
    );
  let warningMsg =
    Json.Decode.(json |> optional(field("warningMsg", string)));

  switch (status, ratingHistoryList, warningMsg) {
  | ("success", Some(ratingHistoryList), None) => SUCCESS(ratingHistoryList)
  | ("warning", None, Some(msg)) => WARNING(msg)
  | _ => raise(IllegalCombinationInRatingsHistoryResult)
  };
};