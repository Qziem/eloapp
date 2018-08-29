open Types;

let encode = (winnerLooserNids: winnerLooserNids) =>
  Json.Encode.(
    object_([
      ("winnerUserNid", int(winnerLooserNids.winnerUserNid)),
      ("looserUserNid", int(winnerLooserNids.looserUserNid)),
    ])
  );