import{i as n,n as t,o as E,r as a,s as r,t as o,u as s,v as I,x as A,y as L}from"./common-qids-864699ad0a6a.js";var c=A(L()),$=I(),R=globalThis.NeoWikiGraphConfig.baseIri,S=`${R}prop/`,i=`${R}schema/`,T=`${R}entity/`;function C(e){e&&e.getModel().importLayout({diagram:a(),validateLinks:!0,dataProvider:new E({endpointUrl:globalThis.NeoWikiGraphConfig.sparqlUrl,queryMethod:r.GET},{...s,classTreeQuery:`
          SELECT ?class ?label (COUNT(DISTINCT ?inst) AS ?instcount)
          WHERE {
            ?inst a ?class .
            FILTER(STRSTARTS(STR(?inst), "${T}"))
            FILTER(STRSTARTS(STR(?class), "${i}"))
            BIND(
              REPLACE(STRAFTER(STR(?class), "${i}"), "_", " ")
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
              REPLACE(STRAFTER(STR(?class), "${i}"), "_", " ")
              AS ?label
            )
          }
          GROUP BY ?class ?label
        `,linkTypesQuery:`
          SELECT DISTINCT ?link ?label
          WHERE {
            ?source ?link ?target .
            FILTER(STRSTARTS(STR(?source), "${T}"))
            FILTER(STRSTARTS(STR(?target), "${T}"))
            FILTER(STRSTARTS(STR(?link), "${S}"))
            BIND(
              REPLACE(STRAFTER(STR(?link), "${S}"), "_", " ")
              AS ?label
            )
          }
        `,linkTypesInfoQuery:`
          SELECT ?link ?label WHERE {
            VALUES (?link) { \${ids} }
            BIND(
              REPLACE(STRAFTER(STR(?link), "${S}"), "_", " ")
              AS ?label
            )
          }
        `,propertyInfoQuery:`
          SELECT ?property ?label WHERE {
            VALUES (?property) { \${ids} }
            BIND(
              REPLACE(STRAFTER(STR(?property), "${S}"), "_", " ")
              AS ?label
            )
          }
        `,filterAdditionalRestriction:`
          FILTER(STRSTARTS(STR(?inst), "${T}"))
        `,linkConfigurations:[],openWorldLinks:!0,linksInfoQuery:`
          SELECT DISTINCT ?source ?type ?target
          WHERE {
            VALUES (?source) { \${ids} }
            VALUES (?target) { \${ids} }

            ?source ?type ?target .

            FILTER(STRSTARTS(STR(?source), "${T}"))
            FILTER(STRSTARTS(STR(?target), "${T}"))
            FILTER(STRSTARTS(STR(?type), "${S}"))
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

            FILTER(STRSTARTS(STR(?other), "${T}"))
            FILTER(STRSTARTS(STR(?link), "${S}"))
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
                FILTER(STRSTARTS(STR(?outObject), "${T}"))
              }
            }
            {
              SELECT
                (\${linkId} AS ?link)
                (COUNT(DISTINCT ?inObject) AS ?inCount)
              WHERE {
                ?inObject \${linkId} \${elementIri} .
                FILTER(STRSTARTS(STR(?inObject), "${T}"))
              }
            }
          }
        `,filterRefElementLinkPattern:`
          FILTER(STRSTARTS(STR(?link), "${S}"))
        `})})}var k={ref:C,onSaveDiagram:e=>{let l=e.getModel().exportLayout();window.location.hash=t(l),window.location.reload()},viewOptions:{onIriClick:({iri:e})=>window.open(e,"_blank","noopener,noreferrer")}};o(e=>{(0,$.createRoot)(e).render((0,c.createElement)(n,k))});

//# sourceMappingURL=envendpoint-qids-0802653af0b3.js.map
