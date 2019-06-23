open Js.Promise;

module Make = (Config: ReasonApolloTypes.Config) => {
  [@bs.module] external gql: ReasonApolloTypes.gql = "graphql-tag";
  module Query = ReasonApolloQuery.Make(Config);

  let call = queryMade => {
    let queryObj: ApolloClient.queryObj = {
      "query": gql(. queryMade##query),
      "variables": queryMade##variables,
    };

    ApiClient.instance##query(queryObj)
    |> then_(resp => {
         let converted = resp->Query.convertJsInputToReason;
         resolve(converted.result);
       });
  };
};