open Js.Promise;

let svcUrl = "api/";

/* TODO: Troche slabo ze w dwoch miejscach jest powtorzone to samo (Fetch.RequestInit.make)
 ** Mozna pomyslec zeby to uwspolnic
 */
let svcGeneric = (method, resource, payload) =>
  Fetch.fetchWithInit(
    svcUrl ++ resource,
    Fetch.RequestInit.make(
      ~method_=method,
      ~credentials=SameOrigin,
      ~headers=Fetch.HeadersInit.make({"Content-Type": "application/json"}),
      ~body=Fetch.BodyInit.make(Js.Json.stringify(payload)),
      (),
    ),
  )
  |> then_(Fetch.Response.json);

let svcGet = resource =>
  Fetch.fetchWithInit(
    svcUrl ++ resource,
    Fetch.RequestInit.make(
      ~method_=Get,
      ~credentials=SameOrigin,
      ~headers=Fetch.HeadersInit.make({"Content-Type": "application/json"}),
      (),
    ),
  )
  |> then_(Fetch.Response.json);

let svcPost = svcGeneric(Post);
let svcPut = svcGeneric(Put);
let svcDelete = svcGeneric(Delete);
