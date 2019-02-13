open EloTypes;

let isCodesEqual = (code1, code2) =>
  String.lowercase(code1) === String.lowercase(code2);

let getUserNidFromCode = (code, users: list(user)) => {
  let user = List.find(user => isCodesEqual(user.code, code), users);
  user.userNid;
};

let withPrevValue = fn => {
  let prevValue = ref(None);
  value => {
    let result = fn(value, prevValue^);
    prevValue := Some(value);
    result;
  };
};

let createTitle = subtitle => {j|Elo app - $subtitle|j};