open Types;

let encode = (updateRagins: gameResultState) =>
  Json.Encode.(
    object_([
      ("userWinCode", string(updateRagins.userWinCode)),
      ("userLooseCode", string(updateRagins.userLooseCode)),
    ])
  );