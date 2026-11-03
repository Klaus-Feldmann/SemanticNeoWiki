import{i as e,n as t,o as n,r,s as i,t as a,u as o,v as s,x as c,y as l}from"./common-CPcN3Iil.js";var u=c(l()),d=s(),f=`https://aufeinwort.info/`,p=`${f}prop/`,m=`${f}schema/`,h=`${f}entity/`;function g(e){e&&e.getModel().importLayout({diagram:r(),validateLinks:!0,dataProvider:new n({endpointUrl:`/graph-explorer/sparql?default-graph-uri=urn%3Ax-arq%3AUnionGraph`,queryMethod:i.GET},{...o,classTreeQuery:`
          SELECT ?class ?label (COUNT(DISTINCT ?inst) AS ?instcount)
          WHERE {
            ?inst a ?class .
            FILTER(STRSTARTS(STR(?inst), "${h}"))
            FILTER(STRSTARTS(STR(?class), "${m}"))
            BIND(
              REPLACE(STRAFTER(STR(?class), "${m}"), "_", " ")
              AS ?label
            )
          }
          GROUP BY ?class ?label
          ORDER BY ?label
        `,classInfoQuery:`
          SELECT ?class ?label (COUNT(DISTINCT ?inst) AS ?instcount)
          WHERE {
            VALUES (?class) { \${ids} }
            OPTIONAL { ?inst a ?class . }
            BIND(
              REPLACE(STRAFTER(STR(?class), "${m}"), "_", " ")
              AS ?label
            )
          }
          GROUP BY ?class ?label
        `,linkTypesQuery:`
          SELECT DISTINCT ?link ?label
          WHERE {
            ?source ?link ?target .
            FILTER(STRSTARTS(STR(?source), "${h}"))
            FILTER(STRSTARTS(STR(?target), "${h}"))
            FILTER(STRSTARTS(STR(?link), "${p}"))
            BIND(
              REPLACE(STRAFTER(STR(?link), "${p}"), "_", " ")
              AS ?label
            )
          }
        `,linkTypesInfoQuery:`
          SELECT ?link ?label WHERE {
            VALUES (?link) { \${ids} }
            BIND(
              REPLACE(STRAFTER(STR(?link), "${p}"), "_", " ")
              AS ?label
            )
          }
        `,propertyInfoQuery:`
          SELECT ?property ?label WHERE {
            VALUES (?property) { \${ids} }
            BIND(
              REPLACE(STRAFTER(STR(?property), "${p}"), "_", " ")
              AS ?label
            )
          }
        `,filterAdditionalRestriction:`
          FILTER(STRSTARTS(STR(?inst), "${h}"))
        `,linkConfigurations:[],openWorldLinks:!0,linksInfoQuery:`
          SELECT DISTINCT ?source ?type ?target
          WHERE {
            VALUES (?source) { \${ids} }
            VALUES (?target) { \${ids} }

            ?source ?type ?target .

            FILTER(STRSTARTS(STR(?source), "${h}"))
            FILTER(STRSTARTS(STR(?target), "${h}"))
            FILTER(STRSTARTS(STR(?type), "${p}"))
          }
        `,linkTypesOfQuery:`
          SELECT DISTINCT ?link
          WHERE {
            {
              \${elementIri} ?link ?other .
            }
            UNION
            {
              ?other ?link \${elementIri} .
            }

            FILTER(STRSTARTS(STR(?other), "${h}"))
            FILTER(STRSTARTS(STR(?link), "${p}"))
          }
        `,linkTypesStatisticsQuery:`
          SELECT ?link ?outCount ?inCount
          WHERE {
            {
              SELECT
                (\${linkId} AS ?link)
                (COUNT(DISTINCT ?outObject) AS ?outCount)
              WHERE {
                \${elementIri} \${linkId} ?outObject .
                FILTER(STRSTARTS(STR(?outObject), "${h}"))
              }
            }
            {
              SELECT
                (\${linkId} AS ?link)
                (COUNT(DISTINCT ?inObject) AS ?inCount)
              WHERE {
                ?inObject \${linkId} \${elementIri} .
                FILTER(STRSTARTS(STR(?inObject), "${h}"))
              }
            }
          }
        `,filterRefElementLinkPattern:`
          FILTER(STRSTARTS(STR(?link), "${p}"))
        `})})}var _={ref:g,onSaveDiagram:e=>{let n=e.getModel().exportLayout();window.location.hash=t(n),window.location.reload()},viewOptions:{onIriClick:({iri:e})=>window.open(e,`_blank`,`noopener,noreferrer`)}};a(t=>{(0,d.createRoot)(t).render((0,u.createElement)(e,_))});
//# sourceMappingURL=envendpoint-Qu3prYG8.js.map