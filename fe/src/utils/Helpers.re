open EloTypes;

let compareCodes = (code1, code2) =>
  String.lowercase(code1) === String.lowercase(code2);

let getUserNidFromCode = (code, users: list(user)) => {
  let user = List.find(user => compareCodes(user.code, code), users);
  user.userNid;
};