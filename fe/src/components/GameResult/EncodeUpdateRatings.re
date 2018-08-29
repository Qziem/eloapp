open Types;

let encode = (updateRagins: gameResultState) =>
  Json.Encode.(
    object_([
      ("userWinnerCode", string(updateRagins.userWinnerCode)),
      ("userLooserCode", string(updateRagins.userLooserCode)),
    ])
  );