open Js.Promise;

let svcUrl = "be/api/index.php/";

let svcGeneric = (method, resource, payload) =>
  Fetch.fetchWithInit(
    svcUrl ++ resource,
    Fetch.RequestInit.make(
      ~method_=method,
      ~body=Fetch.BodyInit.make(Js.Json.stringify(payload)),
      ~headers=Fetch.HeadersInit.make({"Content-Type": "application/json"}),
      (),
    ),
  )
  |> then_(Fetch.Response.json);

let svcGet = resource =>
  Fetch.fetch(svcUrl ++ resource) |> then_(Fetch.Response.json);

let svcPost = svcGeneric(Post);
let svcPut = svcGeneric(Put);

let svcDelete = svcGeneric(Delete);