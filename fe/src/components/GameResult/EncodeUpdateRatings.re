open EloTypes;

let encode = (winnerLooserCodes: winnerLooserCodes) =>
  Json.Encode.(
    object_([
      ("winnerUserCode", string(winnerLooserCodes.winnerUserCode)),
      ("looserUserCode", string(winnerLooserCodes.looserUserCode)),
    ])
  );