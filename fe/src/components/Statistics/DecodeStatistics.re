open Json.Decode;

type content = array(Js.Json.t);

let contentKey = "data";
let decode = Json.Decode.array(json => json);
