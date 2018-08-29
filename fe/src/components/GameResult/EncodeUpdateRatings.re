type gameResultState = {
  userWinCode: string,
  userLooseCode: string,
  warningMsg: option(string),
  saving: bool,
};

let encode = (updateRagins: gameResultState) =>
  Json.Encode.(
    object_([
      ("userWinCode", string(updateRagins.userWinCode)),
      ("userLooseCode", string(updateRagins.userLooseCode)),
    ])
  );