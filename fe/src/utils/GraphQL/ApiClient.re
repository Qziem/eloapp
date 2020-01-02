let inMemoryCache = ApolloInMemoryCache.createInMemoryCache();

let httpLink = ApolloLinks.createHttpLink(~uri="/api/graphql", ());

let instance =
  ReasonApollo.createApolloClient(~link=httpLink, ~cache=inMemoryCache, ());